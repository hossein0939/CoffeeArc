<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Services\Zarinpal;

final class PaymentController
{
    public function callback(): void
    {
        $authority = (string) (input('Authority') ?? input('authority') ?? '');
        $status = (string) (input('Status') ?? input('status') ?? '');

        if ($authority === '') {
            flash('error', 'اطلاعات پرداخت ناقص است.');
            redirect(app_url('m'));
        }

        $order = Order::findByAuthority($authority);
        if (!$order) {
            flash('error', 'سفارش مرتبط یافت نشد.');
            redirect(app_url('m'));
        }

        if (strtoupper($status) !== 'OK' && !str_starts_with($authority, 'MOCK')) {
            Order::markFailed((int) $order['id']);
            redirect(app_url('order/' . $order['order_code'] . '?pay=failed'));
        }

        $verify = Zarinpal::verify($authority, (int) $order['total']);
        if (!$verify['ok']) {
            Order::markFailed((int) $order['id']);
            redirect(app_url('order/' . $order['order_code'] . '?pay=failed'));
        }

        Order::markPaid((int) $order['id'], $verify['ref_id'] ?? null);
        redirect(app_url('order/' . $order['order_code'] . '?pay=ok'));
    }

    /** Local mock gateway page when merchant=TEST */
    public function mock(): void
    {
        $authority = (string) input('Authority', '');
        $amount = (int) input('Amount', 0);
        customer_view('customer/pay_mock', [
            'title' => 'پرداخت آزمایشی',
            'authority' => $authority,
            'amount' => $amount,
        ]);
    }

    public function mockPay(): void
    {
        require_csrf();
        $authority = (string) input('Authority', '');
        $ok = (string) input('result', 'ok') === 'ok';
        $url = app_url('pay/callback?Authority=' . urlencode($authority) . '&Status=' . ($ok ? 'OK' : 'NOK'));
        redirect($url);
    }
}
