<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Models\AdminUser;
use App\Models\CafeTable;
use App\Models\Setting;

final class SettingsController
{
    public function index(): void
    {
        Auth::requireLogin();
        admin_view('admin/settings', [
            'title' => 'تنظیمات',
            'settings' => Setting::all(),
            'features' => Setting::featureKeys(),
            'tables' => CafeTable::all(),
            'admin' => Auth::user(),
            'flash_ok' => flash('success'),
            'flash_err' => flash('error'),
        ]);
    }

    public function save(): void
    {
        Auth::requireLogin();
        require_csrf();

        Setting::set('cafe_name', trim((string) input('cafe_name', 'کافه آرک')));
        Setting::set('cafe_name_en', trim((string) input('cafe_name_en', 'Coffee Arc')));
        Setting::set('cafe_phone', trim((string) input('cafe_phone', '')));
        Setting::set('cafe_address', trim((string) input('cafe_address', '')));
        Setting::set('zarinpal_merchant', trim((string) input('zarinpal_merchant', '')));
        Setting::set('zarinpal_sandbox', input('zarinpal_sandbox') ? '1' : '0');

        $sysLang = (string) input('system_lang', 'fa');
        if (!in_array($sysLang, ['fa', 'en'], true)) {
            $sysLang = 'fa';
        }
        Setting::set('system_lang', $sysLang);
        Setting::set('default_lang', $sysLang);

        foreach (array_keys(Setting::featureKeys()) as $key) {
            Setting::set($key, input('feature_' . $key) ? '1' : '0');
        }

        $newPass = (string) input('new_password', '');
        if ($newPass !== '') {
            if (strlen($newPass) < 4) {
                flash('error', 'رمز جدید خیلی کوتاه است.');
                redirect(app_url('admin/settings'));
            }
            AdminUser::updatePassword((int) Auth::id(), $newPass);
        }

        flash('success', 'تنظیمات ذخیره شد.');
        redirect(app_url('admin/settings'));
    }
}
