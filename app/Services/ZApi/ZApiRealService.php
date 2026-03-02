<?php

namespace App\Services\ZApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZApiRealService implements ZApiServiceInterface
{
    protected string $baseUrl = 'https://api.z-api.io/instances';

    protected string $clientToken;

    public function __construct()
    {
        $this->clientToken = config('services.zapi.client_token');
    }

    /**
     * Build the full URL for an endpoint
     */
    protected function buildUrl(string $instanceId, string $endpoint): string
    {
        $token = config('services.zapi.token');

        return "{$this->baseUrl}/{$instanceId}/token/{$token}/{$endpoint}";
    }

    /**
     * Make HTTP request with error handling
     */
    protected function request(string $method, string $url, array $data = []): array
    {
        try {
            $response = Http::withHeaders([
                'Client-Token' => $this->clientToken,
                'Content-Type' => 'application/json',
            ])->$method($url, $data);

            if (! $response->successful()) {
                Log::error('Z-API request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => $response->body(),
                    'status' => $response->status(),
                ];
            }

            return array_merge(['success' => true], $response->json());
        } catch (\Exception $e) {
            Log::error('Z-API exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateQrCode(string $instanceId): array
    {
        $url = $this->buildUrl($instanceId, 'qr-code/image');
        $response = $this->request('get', $url);

        if (! $response['success']) {
            return $response;
        }

        // Z-API returns base64 image directly
        return [
            'success' => true,
            'qrcode' => $response['value'] ?? null,
            'status' => 'waiting_qr',
            'instance_id' => $instanceId,
        ];
    }

    public function getConnectionStatus(string $instanceId): array
    {
        $url = $this->buildUrl($instanceId, 'status');
        $response = $this->request('get', $url);

        if (! $response['success']) {
            return $response;
        }

        $connected = $response['connected'] ?? false;
        $smartphoneConnected = $response['smartphoneConnected'] ?? false;
        $error = $response['error'] ?? null;

        $status = 'disconnected';
        if ($connected && $smartphoneConnected) {
            $status = 'connected';
        } elseif ($connected && ! $smartphoneConnected) {
            $status = 'waiting_smartphone';
        } elseif ($error) {
            $status = 'error';
        }

        return [
            'success' => true,
            'status' => $status,
            'connected' => $connected,
            'smartphoneConnected' => $smartphoneConnected,
            'error' => $error,
            'instance_id' => $instanceId,
            'phone' => $response['phone'] ?? null,
        ];
    }

    public function sendMessage(string $instanceId, string $phone, string $message): array
    {
        $url = $this->buildUrl($instanceId, 'send-text');

        $response = $this->request('post', $url, [
            'phone' => $this->formatPhone($phone),
            'message' => $message,
        ]);

        if (! $response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'messageId' => $response['messageId'] ?? $response['id'] ?? null,
            'zaapId' => $response['zaapId'] ?? null,
            'status' => 'sent',
            'phone' => $phone,
            'timestamp' => time(),
        ];
    }

    public function sendMedia(string $instanceId, string $phone, string $mediaUrl, string $caption = ''): array
    {
        $extension = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));

        $endpoint = match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => 'send-image',
            in_array($extension, ['mp4', 'avi', 'mov', 'mkv']) => 'send-video',
            in_array($extension, ['mp3', 'wav', 'ogg']) => 'send-audio',
            in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip', 'rar']) => "send-document/{$extension}",
            default => "send-document/{$extension}",
        };

        $url = $this->buildUrl($instanceId, $endpoint);

        $payload = [
            'phone' => $this->formatPhone($phone),
        ];

        if ($endpoint === 'send-image') {
            $payload['image'] = $mediaUrl;
            $payload['caption'] = $caption;
        } elseif ($endpoint === 'send-video') {
            $payload['video'] = $mediaUrl;
            $payload['caption'] = $caption;
        } elseif ($endpoint === 'send-audio') {
            $payload['audio'] = $mediaUrl;
        } else {
            // Documents
            $payload['document'] = $mediaUrl;
            $payload['fileName'] = basename($mediaUrl);
            if ($caption) {
                $payload['caption'] = $caption;
            }
        }

        $response = $this->request('post', $url, $payload);

        if (! $response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'messageId' => $response['messageId'] ?? $response['id'] ?? null,
            'zaapId' => $response['zaapId'] ?? null,
            'status' => 'sent',
            'phone' => $phone,
            'mediaUrl' => $mediaUrl,
            'timestamp' => time(),
        ];
    }

    public function instanceExists(string $instanceId): bool
    {
        $status = $this->getConnectionStatus($instanceId);

        return $status['success'] ?? false;
    }

    public function disconnect(string $instanceId): array
    {
        $url = $this->buildUrl($instanceId, 'disconnect');
        $response = $this->request('get', $url);

        if (! $response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'status' => 'disconnected',
            'instance_id' => $instanceId,
        ];
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace("/\D/", '', $phone);

        if (strlen($phone) < 10 || str_starts_with($phone, '0')) {
            $phone = '55'.ltrim($phone, '0');
        }

        return $phone;
    }
}
