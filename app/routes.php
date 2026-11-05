<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CustomersController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DiscountAdminController;
use App\Controllers\Admin\MenuAdminController;
use App\Controllers\Admin\PosController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\TableAdminController;
use App\Controllers\CheckoutController;
use App\Controllers\InstallController;
use App\Controllers\MenuController;
use App\Controllers\PaymentController;
use App\Helpers\Router;
use App\Config\App;

$router = new Router();

$installed = false;
try {
    $installed = App::isInstalled() || is_file(base_path('storage/installed.lock'));
} catch (Throwable) {
    $installed = false;
}

if (!$installed) {
    $router->get('/', [InstallController::class, 'index']);
    $router->get('install', [InstallController::class, 'index']);
    $router->post('install', [InstallController::class, 'run']);
} else {
    $router->get('/', static function () {
        redirect(app_url('m'));
    });
    $router->get('install', static function () {
        redirect(app_url('admin'));
    });
}

// Customer
$router->get('m', [MenuController::class, 'index']);
$router->get('api/product/{id}', [MenuController::class, 'product']);
$router->get('checkout', [CheckoutController::class, 'show']);
$router->post('checkout', [CheckoutController::class, 'place']);
$router->get('order/{code}', [CheckoutController::class, 'result']);

// Payment
$router->get('pay/callback', [PaymentController::class, 'callback']);
$router->get('pay/mock', [PaymentController::class, 'mock']);
$router->post('pay/mock', [PaymentController::class, 'mockPay']);

// Admin auth
$router->get('admin/login', [AuthController::class, 'loginForm']);
$router->post('admin/login', [AuthController::class, 'login']);
$router->get('admin/logout', [AuthController::class, 'logout']);

// Admin
$router->get('admin', [DashboardController::class, 'index']);
$router->get('admin/orders/{id}', [DashboardController::class, 'show']);
$router->post('admin/orders/{id}/status', [DashboardController::class, 'updateStatus']);
$router->post('admin/orders/{id}/paid', [DashboardController::class, 'markPaid']);

$router->get('admin/pos', [PosController::class, 'index']);
$router->post('admin/pos', [PosController::class, 'place']);

$router->get('admin/menu', [MenuAdminController::class, 'index']);
$router->post('admin/menu/category', [MenuAdminController::class, 'saveCategory']);
$router->post('admin/menu/category/delete', [MenuAdminController::class, 'deleteCategory']);
$router->post('admin/menu/reorder-categories', [MenuAdminController::class, 'reorderCategories']);
$router->post('admin/menu/reorder-products', [MenuAdminController::class, 'reorderProducts']);
$router->get('admin/menu/product/new', [MenuAdminController::class, 'editProduct']);
$router->get('admin/menu/product/{id}', [MenuAdminController::class, 'editProduct']);
$router->post('admin/menu/product', [MenuAdminController::class, 'saveProduct']);
$router->post('admin/menu/product/toggle', [MenuAdminController::class, 'toggleProduct']);
$router->post('admin/menu/product/delete', [MenuAdminController::class, 'deleteProduct']);

$router->get('admin/tables', [TableAdminController::class, 'index']);
$router->post('admin/tables', [TableAdminController::class, 'save']);
$router->post('admin/tables/status', [TableAdminController::class, 'setStatus']);
$router->post('admin/tables/delete', [TableAdminController::class, 'delete']);

$router->get('admin/discounts', [DiscountAdminController::class, 'index']);
$router->post('admin/discounts', [DiscountAdminController::class, 'save']);
$router->post('admin/discounts/delete', [DiscountAdminController::class, 'delete']);

$router->get('admin/customers', [CustomersController::class, 'index']);
$router->get('admin/customers/export', [CustomersController::class, 'export']);

$router->get('admin/settings', [SettingsController::class, 'index']);
$router->post('admin/settings', [SettingsController::class, 'save']);

return $router;
