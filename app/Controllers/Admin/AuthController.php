<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Models\AdminUser;

final class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect(app_url('admin'));
        }
        view('admin/login', [
            'title' => 'ورود مدیر',
            'error' => flash('error'),
        ], 'layouts/install');
    }

    public function login(): void
    {
        require_csrf();
        $username = trim((string) input('username', ''));
        $password = (string) input('password', '');
        $user = AdminUser::verify($username, $password);
        if (!$user) {
            flash('error', 'نام کاربری یا رمز عبور اشتباه است.');
            redirect(app_url('admin/login'));
        }
        Auth::login($user);
        redirect(app_url('admin'));
    }

    public function logout(): void
    {
        Auth::logout();
        redirect(app_url('admin/login'));
    }
}
