<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Get dynamic config value from Setting model or config fallback.
     */
    public static function getConfig(string $key, mixed $default = null): mixed
    {
        return Setting::get("whatsapp_{$key}", config("whatsapp.{$key}", $default));
    }

    /**
     * Get configured HTTP Client with Basic Auth, Timeout, & X-Device-Id header.
     */
    protected static function client()
    {
        $baseUrl   = rtrim(static::getConfig('url', 'https://wagateway.surakana.my.id'), '/');
        $auth      = static::getConfig('auth', 'admin:admin');
        $deviceId  = static::getConfig('device_id', '8a744703-b90a-4690-b911-b1b8f2523963');
        $verifySsl = filter_var(static::getConfig('verify_ssl', true), FILTER_VALIDATE_BOOLEAN);
        $timeout   = (int) static::getConfig('timeout', 30);

        [$username, $password] = explode(':', $auth . ':');

        $headers = [
            'Accept' => 'application/json',
        ];

        if (!empty($deviceId)) {
            $headers['X-Device-Id'] = $deviceId;
            $headers['Device-Id']   = $deviceId;
        }

        $client = Http::baseUrl($baseUrl)
            ->timeout($timeout)
            ->withHeaders($headers)
            ->withBasicAuth($username, $password);

        if (!$verifySsl) {
            $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Format Indonesian phone numbers to standard 628xxxxxxxx.
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Strip non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Check gateway server health and device connection status.
     */
    public static function checkHealth(): array
    {
        try {
            $enabled = filter_var(static::getConfig('enabled', true), FILTER_VALIDATE_BOOLEAN);
            if (!$enabled) {
                return ['success' => false, 'message' => 'WhatsApp Gateway is currently disabled in settings.'];
            }

            // 1. Try GET /app/devices
            $response = static::client()->get('/app/devices');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'   => true,
                    'connected' => true,
                    'data'      => $data,
                    'message'   => 'Gateway server is online and device is connected.',
                ];
            }

            // 2. Fallback: GET /user/my/profile
            $statusResp = static::client()->get('/user/my/profile');
            if ($statusResp->successful()) {
                return [
                    'success'   => true,
                    'connected' => true,
                    'data'      => $statusResp->json(),
                    'message'   => 'Gateway is connected to WhatsApp.',
                ];
            }

            // 3. Fallback: GET /app/user/about
            $aboutResp = static::client()->get('/app/user/about');
            if ($aboutResp->successful()) {
                return [
                    'success'   => true,
                    'connected' => true,
                    'data'      => $aboutResp->json(),
                    'message'   => 'Gateway device is online and logged in.',
                ];
            }

            // If 400 Bad Request, include body response for debugging
            $errorBody = $response->body();
            Log::warning('WhatsApp Gateway Check Failed: HTTP ' . $response->status() . ' - Body: ' . $errorBody);

            return [
                'success'   => false,
                'connected' => false,
                'message'   => 'Gateway server responded with HTTP status ' . $response->status() . ($errorBody ? ": {$errorBody}" : ''),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp Gateway Health Check Error: ' . $e->getMessage());
            return [
                'success'   => false,
                'connected' => false,
                'message'   => 'Connection Failed: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Send text message to a WhatsApp number.
     */
    public static function sendTextMessage(string $phone, string $message): array
    {
        try {
            $enabled = filter_var(static::getConfig('enabled', true), FILTER_VALIDATE_BOOLEAN);
            if (!$enabled) {
                return ['success' => false, 'message' => 'WhatsApp Gateway is disabled.'];
            }

            $formattedPhone = static::formatPhoneNumber($phone);

            $payload = [
                'phone'   => $formattedPhone,
                'message' => $message,
            ];

            // Try standard /send/message endpoint
            $response = static::client()->post('/send/message', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                    'message' => 'Message successfully sent to ' . $formattedPhone,
                ];
            }

            // Fallback to /message/send-text if API format differs
            $fallbackResp = static::client()->post('/message/send-text', $payload);
            if ($fallbackResp->successful()) {
                return [
                    'success' => true,
                    'data'    => $fallbackResp->json(),
                    'message' => 'Message successfully sent to ' . $formattedPhone,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send message. Server returned HTTP ' . $response->status() . ': ' . $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp Send Message Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch QR Code for device pairing/login.
     */
    public static function fetchQrCode(): array
    {
        try {
            $response = static::client()->get('/app/login');

            if ($response->successful()) {
                $data = $response->json();
                
                // Return base64 or qr link if available
                $qr = $data['results']['qr_link'] ?? $data['results']['qr_code'] ?? $data['qr_code'] ?? null;
                if ($qr) {
                    return ['success' => true, 'qr' => $qr, 'data' => $data];
                }
                return ['success' => true, 'data' => $data];
            }

            return ['success' => false, 'message' => 'Failed to fetch QR code: HTTP ' . $response->status()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error fetching QR code: ' . $e->getMessage()];
        }
    }

    /**
     * Request 8-character Pairing Code for phone linking.
     */
    public static function requestPairingCode(string $phone): array
    {
        try {
            $formattedPhone = static::formatPhoneNumber($phone);
            $response = static::client()->get('/app/pair-code', ['phone' => $formattedPhone]);

            if ($response->successful()) {
                $data = $response->json();
                $code = $data['results']['pair_code'] ?? $data['code'] ?? $data['results']['code'] ?? null;
                return [
                    'success' => true,
                    'code'    => $code,
                    'data'    => $data,
                    'message' => 'Pairing code generated for ' . $formattedPhone,
                ];
            }

            return ['success' => false, 'message' => 'Failed to get pairing code: HTTP ' . $response->status() . ' ' . $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error requesting pairing code: ' . $e->getMessage()];
        }
    }
}
