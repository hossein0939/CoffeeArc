<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CafeTable;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderBuilder;
use App\Services\Zarinpal;

final class CheckoutController
{
    public function show(): void
    {
        if (!Setting::enabled('online_ordering')) {
            flash('error', __('menu_only'));
            redirect(app_url('m'));
        }

        $needPhone = Setting::enabled('customer_phone');
        $onlinePayment = Setting::enabled('online_payment');
        $payAtCafe = Setting::enabled('pay_at_cafe');
        $selectedTableId = $_SESSION['selected_table_id'] ?? null;
        $tableRequired = Setting::enabled('table_required');
        $needTable = $tableRequired && !$selectedTableId;

        // Fastest path: no phone, no online choice needed, table already known → JS places instantly
        // If somehow landed here, still render minimal confirm.
        $mode = 'confirm';
        if ($needPhone && $onlinePayment) {
            $mode = 'phone_and_pay';
        } elseif ($needPhone) {
            $mode = 'phone_only';
        } elseif ($onlinePayment) {
            $mode = 'pay_only';
        } else {
            $mode = 'confirm';
        }

        customer_view('customer/checkout', [
            'title' => __('checkout'),
            'tables' => CafeTable::all(),
            'selectedTableId' => $selectedTableId,
            'onlinePayment' => $onlinePayment,
            'payAtCafe' => $payAtCafe,
            'customerPhone' => $needPhone,
            'phoneRequired' => $needPhone || Setting::enabled('customer_phone_required'),
            'tableRequired' => $tableRequired,
            'needTable' => $needTable,
            'mode' => $mode,
        ]);
    }

    public function place(): void
    {
        require_csrf();

        if (!Setting::enabled('online_ordering')) {
            json_response(['ok' => false, 'error' => __('menu_only')], 403);
        }

        $now = time();
        if (!empty($_SESSION['last_order_at']) && ($now - (int) $_SESSION['last_order_at']) < 3) {
            json_response(['ok' => false, 'error' => 'لطفاً چند ثانیه صبر کنید.'], 429);
        }

        $rawCart = input('cart', '[]');
        $cart = is_string($rawCart) ? json_decode($rawCart, true) : $rawCart;
        if (!is_array($cart) || !$cart) {
            json_response(['ok' => false, 'error' => 'سبد خرید خالی است.']);
        }

        $tableId = input('table_id') !== null && input('table_id') !== ''
            ? (int) input('table_id')
            : (isset($_SESSION['selected_table_id']) ? (int) $_SESSION['selected_table_id'] : null);

        if (Setting::enabled('table_required') && !$tableId) {
            json_response(['ok' => false, 'error' => 'انتخاب میز الزامی است.', 'need_checkout' => true]);
        }
        if ($tableId && !CafeTable::find($tableId)) {
            json_response(['ok' => false, 'error' => 'میز نامعتبر است.']);
        }

        $needPhone = Setting::enabled('customer_phone');
        $phone = normalize_phone((string) input('customer_phone', ''));
        if ($needPhone && !$phone) {
            json_response(['ok' => false, 'error' => 'شماره تماس الزامی است.', 'need_checkout' => true]);
        }

        $onlineOk = Setting::enabled('online_payment');
        $cafeOk = Setting::enabled('pay_at_cafe');
        $payMethod = (string) input('pay_method', '');

        if ($payMethod === '') {
            $payMethod = $onlineOk && !$cafeOk ? 'online' : 'cafe';
        }
        if ($payMethod === 'online' && !$onlineOk) {
            $payMethod = 'cafe';
        }
        if ($payMethod === 'cafe' && !$cafeOk && $onlineOk) {
            $payMethod = 'online';
        }
        if (!in_array($payMethod, ['online', 'cafe'], true)) {
            json_response(['ok' => false, 'error' => 'روش پرداخت نامعتبر است.']);
        }
        if (!$onlineOk && !$cafeOk) {
            json_response(['ok' => false, 'error' => 'هیچ روش پرداختی فعال نیست.']);
        }

        // If online payment is available and user didn't choose, force checkout UI
        if ($onlineOk && $cafeOk && input('pay_method') === null && input('instant') === '1') {
            json_response(['ok' => false, 'need_checkout' => true, 'error' => 'روش پرداخت را انتخاب کنید.']);
        }

        try {
            $built = OrderBuilder::buildItems($cart);
            $disc = OrderBuilder::applyDiscount($built['subtotal'], trim((string) input('discount_code', '')) ?: null);
        } catch (\Throwable $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()]);
        }

        $orderId = Order::create([
            'order_code' => generate_order_code(),
            'table_id' => $tableId,
            'source' => 'customer',
            'status' => 'new',
            'pay_method' => $payMethod,
            'pay_status' => $payMethod === 'online' ? 'pending' : 'unpaid',
            'customer_name' => trim((string) input('customer_name', '')) ?: null,
            'customer_phone' => $phone ?: null,
            'notes' => Setting::enabled('order_notes') ? (trim((string) input('notes', '')) ?: null) : null,
            'discount_code' => $disc['discount_code'],
            'discount_amount' => $disc['discount_amount'],
            'subtotal' => $built['subtotal'],
            'total' => $disc['total'],
        ], $built['items']);

        $_SESSION['last_order_at'] = $now;

        $order = Order::find($orderId);

        if ($payMethod === 'online') {
            $result = Zarinpal::request(
                (int) $order['total'],
                'پرداخت سفارش ' . $order['order_code'],
                app_url('pay/callback'),
                $phone ?: null
            );
            if (!$result['ok']) {
                Order::markFailed($orderId);
                json_response(['ok' => false, 'error' => $result['error'] ?? 'خطای درگاه']);
            }
            Order::setPaymentPending($orderId, $result['authority']);
            json_response([
                'ok' => true,
                'redirect' => $result['url'],
                'order_code' => $order['order_code'],
            ]);
        }

        json_response([
            'ok' => true,
            'redirect' => app_url('order/' . $order['order_code']),
            'order_code' => $order['order_code'],
            'message' => __('order_placed'),
        ]);
    }

    public function result(string $code): void
    {
        $order = Order::findByCode($code);
        if (!$order) {
            http_response_code(404);
            echo 'سفارش یافت نشد.';
            return;
        }
        customer_view('customer/result', [
            'title' => __('order_code'),
            'order' => $order,
            'items' => Order::items((int) $order['id']),
        ]);
    }
}
