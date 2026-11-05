<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;

final class MenuController
{
    public function index(): void
    {
        $tableNumber = trim((string) input('t', ''));
        $token = trim((string) input('token', ''));
        $selectedTable = null;

        // Table sticks only when opened via QR/table link (?t=..). Plain domain/menu clears it.
        if ($tableNumber !== '') {
            $selectedTable = CafeTable::findByNumber($tableNumber);
            if ($selectedTable && Setting::enabled('qr_per_table') && $token !== '') {
                if (!hash_equals($selectedTable['qr_token'], $token)) {
                    $selectedTable = null;
                }
            } elseif ($selectedTable && Setting::enabled('qr_per_table') && $token === '') {
                // Number alone without token: accept for convenience, still bind session
            }
            if ($selectedTable) {
                $_SESSION['selected_table_id'] = (int) $selectedTable['id'];
                $_SESSION['selected_table_number'] = $selectedTable['table_number'];
            } else {
                unset($_SESSION['selected_table_id'], $_SESSION['selected_table_number']);
            }
        } else {
            unset($_SESSION['selected_table_id'], $_SESSION['selected_table_number']);
        }

        $categories = Category::all(true);
        $products = Product::allActive();
        $grouped = [];
        foreach ($products as $p) {
            $grouped[(int) $p['category_id']][] = $p;
        }

        $cafeName = lang_pick(Setting::get('cafe_name', 'کافه آرک'), Setting::get('cafe_name_en', 'Coffee Arc'));

        customer_view('customer/menu', [
            'title' => $cafeName,
            'categories' => $categories,
            'grouped' => $grouped,
            'selectedTable' => $selectedTable,
            'tables' => CafeTable::all(),
            'features' => Setting::all(),
            'orderingEnabled' => Setting::enabled('online_ordering'),
            'customization' => Setting::enabled('item_customization'),
        ]);
    }

    public function product(string $id): void
    {
        $product = Product::withOptions((int) $id);
        if (!$product || !(int) $product['is_available']) {
            json_response(['ok' => false, 'error' => 'Not found'], 404);
        }
        $category = Category::find((int) $product['category_id']);
        $price = has_price($product['base_price'] ?? null) ? (int) $product['base_price'] : null;
        json_response([
            'ok' => true,
            'product' => [
                'id' => (int) $product['id'],
                'name' => lang_pick($product['name'], $product['name_en'] ?? null),
                'name_en' => $product['name_en'],
                'description' => lang_pick($product['description'] ?? null, $product['description_en'] ?? null),
                'base_price' => $price,
                'has_price' => $price !== null,
                'image' => product_image_url($product, $category),
                'sizes' => array_map(static fn($s) => [
                    'id' => (int) $s['id'],
                    'name' => $s['name'],
                    'price_modifier' => (int) $s['price_modifier'],
                ], $product['sizes']),
                'addons' => array_map(static fn($a) => [
                    'id' => (int) $a['id'],
                    'name' => $a['name'],
                    'price' => (int) $a['price'],
                ], $product['addons']),
            ],
            'customization' => Setting::enabled('item_customization'),
        ]);
    }
}
