<?php

namespace App\Services\Meta;

use Illuminate\Support\Str;

class MetaGraphApiMockService implements MetaGraphApiServiceInterface
{
    public function sendInstagramMessage(string $recipientId, string $text, string $accessToken): array
    {
        return [
            'success' => true,
            'message_id' => 'mock_ig_'.Str::uuid(),
            'recipient_id' => $recipientId,
        ];
    }

    public function sendFacebookMessage(string $pageId, string $recipientId, string $text, string $accessToken): array
    {
        return [
            'success' => true,
            'message_id' => 'mock_fb_'.Str::uuid(),
            'recipient_id' => $recipientId,
        ];
    }

    public function sendMedia(string $pageId, string $recipientId, string $mediaUrl, string $mediaType, string $accessToken): array
    {
        return [
            'success' => true,
            'message_id' => 'mock_media_'.Str::uuid(),
            'recipient_id' => $recipientId,
            'attachment_id' => 'mock_attachment_'.Str::uuid(),
        ];
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
        return [
            'success' => true,
            'id' => $pageId,
            'name' => 'Mock Page',
        ];
    }

    public function verifySignature(string $payload, string $signature): bool
    {
        return true;
    }

    public function extendToken(string $shortLivedToken): array
    {
        return ['access_token' => 'mock_long_token', 'expires_in' => 5184000];
    }

    public function getPages(string $userAccessToken): array
    {
        return [
            'data' => [
                ['id' => 'mock_page_1', 'name' => 'Mock Page', 'access_token' => 'mock_page_token'],
            ],
        ];
    }

    public function getInstagramBusinessAccount(string $pageId, string $pageAccessToken): ?string
    {
        return null;
    }

    public function subscribePage(string $pageId, string $pageAccessToken): bool
    {
        return true;
    }
}
