<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Setting;

final class OrderBuilder
{
    /**
     * @param array<int, array{product_id:int, size_id?:int|null, addon_ids?:int[], quantity:int, notes?:string}> $cart
     */
    public static function buildItems(array $cart): array
    {
        $items = [];
        $subtotal = 0;
        $customization = Setting::enabled('item_customization');

        foreach ($cart as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $qty = max(1, (int) ($line['quantity'] ?? 1));
            $product = Product::find($productId);
            if (!$product || !(int) $product['is_available']) {
                throw new \RuntimeException('محصول ناموجود یا غیرفعال است.');
            }

            $unit = has_price($product['base_price'] ?? null) ? (int) $product['base_price'] : 0;
            $sizeName = null;
            $addons = [];

            if ($customization) {
                $sizeId = isset($line['size_id']) ? (int) $line['size_id'] : 0;
                if ($sizeId > 0) {
                    foreach (Product::sizes($productId) as $size) {
                        if ((int) $size['id'] === $sizeId) {
                            $unit += (int) $size['price_modifier'];
                            $sizeName = $size['name'];
                            break;
                        }
                    }
                }
                $addonIds = array_map('intval', $line['addon_ids'] ?? []);
                if ($addonIds) {
                    foreach (Product::addons($productId) as $addon) {
                        if (in_array((int) $addon['id'], $addonIds, true)) {
                            $unit += (int) $addon['price'];
                            $addons[] = ['name' => $addon['name'], 'price' => (int) $addon['price']];
                        }
                    }
                }
            }

            $lineTotal = $unit * $qty;
            $subtotal += $lineTotal;
            $items[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'size_name' => $sizeName,
                'addons' => $addons,
                'unit_price' => $unit,
                'quantity' => $qty,
                'line_total' => $lineTotal,
                'notes' => $line['notes'] ?? null,
            ];
        }

        if (!$items) {
            throw new \RuntimeException('سبد خرید خالی است.');
        }

        return ['items' => $items, 'subtotal' => $subtotal];
    }

    public static function applyDiscount(int $subtotal, ?string $code): array
    {
        if (!Setting::enabled('discounts') || !$code) {
            return ['discount_code' => null, 'discount_amount' => 0, 'total' => $subtotal];
        }
        $discount = Discount::findByCode($code);
        if (!$discount) {
            throw new \RuntimeException('کد تخفیف معتبر نیست.');
        }
        $amount = Discount::calcAmount($discount, $subtotal);
        return [
            'discount_code' => $discount['code'],
            'discount_amount' => $amount,
            'total' => max(0, $subtotal - $amount),
        ];
    }
}
