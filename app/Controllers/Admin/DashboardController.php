<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Models\Order;
use App\Models\Setting;

final class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();
        $status = (string) input('status', 'all');
        $orders = Order::list($status === 'all' ? null : $status, 80);
        admin_view('admin/orders', [
            'title' => 'سفارش‌های زنده',
            'orders' => $orders,
            'status' => $status,
            'stats' => Setting::enabled('simple_stats') ? Order::stats() : null,
            'admin' => Auth::user(),
        ]);
    }

    public function updateStatus(string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        $status = (string) input('status', '');
        $allowed = ['new', 'preparing', 'ready', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            json_response(['ok' => false, 'error' => 'وضعیت نامعتبر'], 422);
        }
        Order::updateStatus((int) $id, $status);
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            json_response(['ok' => true]);
        }
        redirect(app_url('admin'));
    }

    public function markPaid(string $id): void
    {
        Auth::requireLogin();
        require_csrf();
        Order::markPaid((int) $id);
        redirect(app_url('admin'));
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $order = Order::find((int) $id);
        if (!$order) {
            http_response_code(404);
            echo 'یافت نشد';
            return;
        }
        admin_view('admin/order_show', [
            'title' => 'جزئیات سفارش',
            'order' => $order,
            'items' => Order::items((int) $order['id']),
            'admin' => Auth::user(),
        ]);
    }
}
