<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Helpers\Auth;
use App\Helpers\Uploader;
use App\Models\Category;
use App\Models\Product;

final class MenuAdminController
{
    public function index(): void
    {
        Auth::requireLogin();
        $categories = Category::all();
        $products = Product::allAdmin();
        $grouped = [];
        foreach ($products as $p) {
            $grouped[(int) $p['category_id']][] = $p;
        }
        admin_view('admin/menu', [
            'title' => 'مدیریت منو',
            'categories' => $categories,
            'grouped' => $grouped,
            'admin' => Auth::user(),
            'flash_ok' => flash('success'),
            'flash_err' => flash('error'),
            'csrf' => csrf_token(),
        ]);
    }

    public function saveCategory(): void
    {
        Auth::requireLogin();
        require_csrf();
        $id = (int) input('id', 0);
        $imagePath = null;
        try {
            if (!empty($_FILES['image']['name'])) {
                $imagePath = Uploader::image($_FILES['image'], 'categories');
            }
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect(app_url('admin/menu'));
        }

        $data = [
            'name' => trim((string) input('name', '')),
            'name_en' => trim((string) input('name_en', '')) ?: null,
            'sort_order' => (int) input('sort_order', 0),
            'is_active' => input('is_active') ? 1 : 0,
        ];
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }
        if ($data['name'] === '') {
            flash('error', 'نام دسته الزامی است.');
            redirect(app_url('admin/menu'));
        }
        if ($id > 0) {
            Category::update($id, $data);
        } else {
            Category::create($data);
        }
        flash('success', 'دسته ذخیره شد.');
        redirect(app_url('admin/menu'));
    }

    public function deleteCategory(): void
    {
        Auth::requireLogin();
        require_csrf();
        try {
            Category::delete((int) input('id'));
            flash('success', 'دسته حذف شد.');
        } catch (\Throwable $e) {
            flash('error', 'امکان حذف دسته وجود ندارد (احتمالاً محصول دارد).');
        }
        redirect(app_url('admin/menu'));
    }

    public function reorderCategories(): void
    {
        Auth::requireLogin();
        require_csrf();
        $ids = input('ids', []);
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: [];
        }
        Category::reorder(array_map('intval', (array) $ids));
        json_response(['ok' => true]);
    }

    public function reorderProducts(): void
    {
        Auth::requireLogin();
        require_csrf();
        $ids = input('ids', []);
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: [];
        }
        Product::reorder(array_map('intval', (array) $ids));
        json_response(['ok' => true]);
    }

    public function editProduct(string $id = '0'): void
    {
        Auth::requireLogin();
        $pid = (int) $id;
        $product = $pid ? Product::withOptions($pid) : null;
        admin_view('admin/product_form', [
            'title' => $product ? 'ویرایش محصول' : 'محصول جدید',
            'product' => $product,
            'categories' => Category::all(),
            'admin' => Auth::user(),
        ]);
    }

    public function saveProduct(): void
    {
        Auth::requireLogin();
        require_csrf();
        $id = (int) input('id', 0);
        $imagePath = null;
        try {
            if (!empty($_FILES['image']['name'])) {
                $imagePath = Uploader::image($_FILES['image']);
            }
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect(app_url('admin/menu/product/' . ($id ?: 'new')));
        }

        $priceRaw = trim((string) input('base_price', ''));
        $data = [
            'category_id' => (int) input('category_id'),
            'name' => trim((string) input('name', '')),
            'name_en' => trim((string) input('name_en', '')) ?: null,
            'description' => trim((string) input('description', '')) ?: null,
            'description_en' => trim((string) input('description_en', '')) ?: null,
            'base_price' => $priceRaw === '' ? null : (int) $priceRaw,
            'is_available' => input('is_available') ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
            'image_path' => $imagePath,
        ];
        if ($data['name'] === '') {
            flash('error', 'نام محصول الزامی است.');
            redirect(app_url('admin/menu'));
        }

        if ($id > 0) {
            Product::update($id, $data);
            $productId = $id;
        } else {
            $productId = Product::create($data);
        }

        $sizes = [];
        $sizeNames = $_POST['size_name'] ?? [];
        $sizeMods = $_POST['size_mod'] ?? [];
        foreach ($sizeNames as $i => $n) {
            $sizes[] = ['name' => $n, 'price_modifier' => (int) ($sizeMods[$i] ?? 0)];
        }
        Product::replaceSizes($productId, $sizes);

        $addons = [];
        $addonNames = $_POST['addon_name'] ?? [];
        $addonPrices = $_POST['addon_price'] ?? [];
        foreach ($addonNames as $i => $n) {
            $addons[] = ['name' => $n, 'price' => (int) ($addonPrices[$i] ?? 0)];
        }
        Product::replaceAddons($productId, $addons);

        flash('success', 'محصول ذخیره شد.');
        redirect(app_url('admin/menu'));
    }

    public function toggleProduct(): void
    {
        Auth::requireLogin();
        require_csrf();
        $id = (int) input('id');
        $available = input('available') === '1';
        Product::setAvailability($id, $available);
        redirect(app_url('admin/menu'));
    }

    public function deleteProduct(): void
    {
        Auth::requireLogin();
        require_csrf();
        Product::softDelete((int) input('id'));
        flash('success', 'محصول حذف شد.');
        redirect(app_url('admin/menu'));
    }
}
