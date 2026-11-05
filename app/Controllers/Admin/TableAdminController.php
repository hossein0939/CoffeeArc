<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Models\CafeTable;
use App\Models\Setting;

final class TableAdminController
{
    public function index(): void
    {
        Auth::requireLogin();
        if (!Setting::enabled('table_board')) {
            flash('error', 'مدیریت میزها غیرفعال است.');
            redirect(app_url('admin'));
        }
        admin_view('admin/tables', [
            'title' => 'میزها',
            'tables' => CafeTable::all(),
            'admin' => Auth::user(),
            'qrEnabled' => Setting::enabled('qr_per_table'),
            'flash_ok' => flash('success'),
        ]);
    }

    public function save(): void
    {
        Auth::requireLogin();
        require_csrf();
        $id = (int) input('id', 0);
        $data = [
            'table_number' => trim((string) input('table_number', '')),
            'seats' => (int) input('seats', 2),
            'status' => (string) input('status', 'free'),
        ];
        if ($data['table_number'] === '') {
            flash('error', 'شماره میز الزامی است.');
            redirect(app_url('admin/tables'));
        }
        if ($id > 0) {
            CafeTable::update($id, $data);
        } else {
            CafeTable::create($data);
        }
        flash('success', 'میز ذخیره شد.');
        redirect(app_url('admin/tables'));
    }

    public function setStatus(): void
    {
        Auth::requireLogin();
        require_csrf();
        $id = (int) input('id');
        $status = (string) input('status', 'free');
        if (!in_array($status, ['free', 'occupied', 'reserved'], true)) {
            $status = 'free';
        }
        CafeTable::setStatus($id, $status);
        redirect(app_url('admin/tables'));
    }

    public function delete(): void
    {
        Auth::requireLogin();
        require_csrf();
        CafeTable::delete((int) input('id'));
        flash('success', 'میز حذف شد.');
        redirect(app_url('admin/tables'));
    }
}
