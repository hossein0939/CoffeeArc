<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderBuilder;

final class PosController
{
    public function index(): void
    {
        Auth::requireLogin();
        if (!Setting::enabled('staff_pos')) {
            flash('error', 'POS غیرفعال است.');
            redirect(app_url('admin'));
        }

        $categories = Category::all(true);
        $products = Product::allActive();
        $grouped = [];
        foreach ($products as $p) {
            $grouped[(int) $p['category_id']][] = $p;
        }

        admin_view('admin/pos', [
            'title' => 'ثبت سفارش',
            'categories' => $categories,
            'grouped' => $grouped,
            'tables' => CafeTable::all(),
            'customization' => Setting::enabled('item_customization'),
            'discounts' => Setting::enabled('discounts'),
            'admin' => Auth::user(),
        ]);
    }

    public function place(): void
    {
        Auth::requireLogin();
        require_csrf();
        if (!Setting::enabled('staff_pos')) {
            json_response(['ok' => false, 'error' => 'POS غیرفعال'], 403);
        }

        $rawCart = input('cart', '[]');
        $cart = is_string($rawCart) ? json_decode($rawCart, true) : $rawCart;
        if (!is_array($cart) || !$cart) {
            json_response(['ok' => false, 'error' => 'سبد خالی است.']);
        }

        $tableId = input('table_id') !== null && input('table_id') !== '' ? (int) input('table_id') : null;
        $payMethod = (string) input('pay_method', 'cafe');
        if (!in_array($payMethod, ['online', 'cafe'], true)) {
            $payMethod = 'cafe';
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
            'source' => 'pos',
            'status' => 'preparing',
            'pay_method' => $payMethod,
            'pay_status' => $payMethod === 'cafe' ? (input('mark_paid') === '1' ? 'paid' : 'unpaid') : 'unpaid',
            'customer_name' => trim((string) input('customer_name', '')) ?: null,
            'customer_phone' => trim((string) input('customer_phone', '')) ?: null,
            'notes' => trim((string) input('notes', '')) ?: null,
            'discount_code' => $disc['discount_code'],
            'discount_amount' => $disc['discount_amount'],
            'subtotal' => $built['subtotal'],
            'total' => $disc['total'],
        ], $built['items']);

        $order = Order::find($orderId);
        json_response(['ok' => true, 'order_code' => $order['order_code'], 'redirect' => app_url('admin')]);
    }
}
