<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

final class Zarinpal
{
    public static function isSandbox(): bool
    {
        return Setting::get('zarinpal_sandbox', env('ZARINPAL_SANDBOX', 'true')) === '1'
            || env('ZARINPAL_SANDBOX', 'true') === 'true';
    }

    public static function merchant(): string
    {
        $fromSettings = Setting::get('zarinpal_merchant', '');
        if ($fromSettings) {
            return $fromSettings;
        }
        return (string) env('ZARINPAL_MERCHANT', '');
    }

    private static function baseUrl(): string
    {
        return self::isSandbox()
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/'
            : 'https://payment.zarinpal.com/pg/v4/payment/';
    }

    private static function startPayUrl(string $authority): string
    {
        $host = self::isSandbox()
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/';
        return $host . $authority;
    }

    public static function request(int $amountToman, string $description, string $callbackUrl, ?string $mobile = null): array
    {
        $merchant = self::merchant();
        if ($merchant === '') {
            return ['ok' => false, 'error' => 'Merchant ID تنظیم نشده است.'];
        }

        // Mock mode for local testing without real gateway
        if ($merchant === 'TEST' || $merchant === 'mock') {
            $authority = 'MOCK' . strtoupper(bin2hex(random_bytes(8)));
            return [
                'ok' => true,
                'authority' => $authority,
                'url' => app_url('pay/mock?Authority=' . urlencode($authority) . '&Amount=' . $amountToman),
                'mock' => true,
            ];
        }

        $payload = [
            'merchant_id' => $merchant,
            'amount' => $amountToman * 10, // Rial
            'callback_url' => $callbackUrl,
            'description' => $description,
        ];
        if ($mobile) {
            $payload['metadata'] = ['mobile' => $mobile];
        }

        $response = self::post(self::baseUrl() . 'request.json', $payload);
        if (!$response || !isset($response['data']['authority'])) {
            $msg = $response['errors']['message'] ?? 'خطا در اتصال به زرین‌پال';
            return ['ok' => false, 'error' => $msg, 'raw' => $response];
        }

        $authority = $response['data']['authority'];
        return [
            'ok' => true,
            'authority' => $authority,
            'url' => self::startPayUrl($authority),
        ];
    }

    public static function verify(string $authority, int $amountToman): array
    {
        $merchant = self::merchant();
        if ($merchant === 'TEST' || $merchant === 'mock' || str_starts_with($authority, 'MOCK')) {
            return [
                'ok' => true,
                'ref_id' => 'MOCKREF' . random_int(10000, 99999),
                'mock' => true,
            ];
        }

        $payload = [
            'merchant_id' => $merchant,
            'amount' => $amountToman * 10,
            'authority' => $authority,
        ];
        $response = self::post(self::baseUrl() . 'verify.json', $payload);
        $code = $response['data']['code'] ?? null;
        if ($code === 100 || $code === 101) {
            return [
                'ok' => true,
                'ref_id' => (string) ($response['data']['ref_id'] ?? ''),
                'code' => $code,
            ];
        }
        return [
            'ok' => false,
            'error' => $response['errors']['message'] ?? 'تأیید پرداخت ناموفق بود.',
            'raw' => $response,
        ];
    }

    private static function post(string $url, array $payload): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            return ['errors' => ['message' => $err ?: 'cURL error']];
        }
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }
}
