<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Models\Discount;
use App\Models\Setting;

final class DiscountAdminController
{
    public function index(): void
    {
        Auth::requireLogin();
        if (!Setting::enabled('discounts')) {
            flash('error', 'تخفیف‌ها غیرفعال است. از تنظیمات فعال کنید.');
            redirect(app_url('admin/settings'));
        }
        admin_view('admin/discounts', [
            'title' => 'تخفیف‌ها',
            'discounts' => Discount::all(),
            'admin' => Auth::user(),
            'flash_ok' => flash('success'),
        ]);
    }

    public function save(): void
    {
        Auth::requireLogin();
        require_csrf();
        $id = (int) input('id', 0);
        $data = [
            'code' => trim((string) input('code', '')),
            'type' => (string) input('type', 'percent'),
            'value' => (int) input('value', 0),
            'is_active' => input('is_active') ? 1 : 0,
        ];
        if ($data['code'] === '' || $data['value'] <= 0) {
            flash('error', 'کد و مقدار معتبر وارد کنید.');
            redirect(app_url('admin/discounts'));
        }
        if ($id > 0) {
            Discount::update($id, $data);
        } else {
            Discount::create($data);
        }
        flash('success', 'تخفیف ذخیره شد.');
        redirect(app_url('admin/discounts'));
    }

    public function delete(): void
    {
        Auth::requireLogin();
        require_csrf();
        Discount::delete((int) input('id'));
        flash('success', 'حذف شد.');
        redirect(app_url('admin/discounts'));
    }
}
