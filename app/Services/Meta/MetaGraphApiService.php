<?php

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaGraphApiService implements MetaGraphApiServiceInterface
{
    protected string $baseUrl = 'https://graph.facebook.com/v22.0';

    public function sendInstagramMessage(string $recipientId, string $text, string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
                'messaging_type' => 'RESPONSE',
            ]);

        if (! $response->successful()) {
            Log::error('Meta Graph API: Instagram send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => $response->json('error.message', $response->body())];
        }

        return array_merge(['success' => true], $response->json());
    }

    public function sendFacebookMessage(string $pageId, string $recipientId, string $text, string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/{$pageId}/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
                'messaging_type' => 'RESPONSE',
            ]);

        if (! $response->successful()) {
            Log::error('Meta Graph API: Facebook Messenger send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => $response->json('error.message', $response->body())];
        }

        return array_merge(['success' => true], $response->json());
    }

    public function sendMedia(string $pageId, string $recipientId, string $mediaUrl, string $mediaType, string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/{$pageId}/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => [
                    'attachment' => [
                        'type' => $mediaType,
                        'payload' => [
                            'url' => $mediaUrl,
                            'is_reusable' => true,
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Meta Graph API: Media send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => $response->json('error.message', $response->body())];
        }

        return array_merge(['success' => true], $response->json());
    }

    public function verifyWebhook(string $mode, string $token, string $challenge): ?string
    {
        if ($mode === 'subscribe' && $token === config('services.meta.webhook_token')) {
            return $challenge;
        }

        return null;
    }

    public function validateToken(string $pageId, string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/{$pageId}", [
                'fields' => 'id,name',
            ]);

        if (! $response->successful()) {
            return ['success' => false, 'error' => $response->json('error.message', $response->body())];
        }

        return array_merge(['success' => true], $response->json());
    }

    public function verifySignature(string $payload, string $signature): bool
    {
        $expectedSignature = 'sha256='.hash_hmac('sha256', $payload, config('services.meta.app_secret', ''));

        return hash_equals($expectedSignature, $signature);
    }

    public function extendToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->baseUrl}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if (! $response->successful()) {
            Log::error('Meta Graph API: Token extension failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['access_token' => $shortLivedToken, 'expires_in' => 0];
        }

        return $response->json();
    }

    public function getPages(string $userAccessToken): array
    {
        $response = Http::get("{$this->baseUrl}/me/accounts", [
            'fields' => 'id,name,access_token',
            'access_token' => $userAccessToken,
        ]);

        if (! $response->successful()) {
            Log::error('Meta Graph API: Get pages failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['data' => []];
        }

        return $response->json();
    }

    public function getInstagramBusinessAccount(string $pageId, string $pageAccessToken): ?string
    {
        $response = Http::get("{$this->baseUrl}/{$pageId}", [
            'fields' => 'instagram_business_account',
            'access_token' => $pageAccessToken,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('instagram_business_account.id');
    }
}
