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

    public function getSenderProfile(string $senderId, string $pageAccessToken, \App\Enums\Channel $channel): array
    {
        return ['name' => 'Mock User', 'profile_pic' => null];
    }

    public function subscribePage(string $pageId, string $pageAccessToken): bool
    {
        return true;
    }

    public function exchangeInstagramCode(string $code, string $redirectUri): array
    {
        return [
            'access_token' => 'mock_ig_short_token',
            'token_type' => 'bearer',
            'user_id' => 'mock_ig_user_123',
        ];
    }

    public function getInstagramLongLivedToken(string $shortLivedToken): array
    {
        return [
            'access_token' => 'mock_ig_long_token',
            'token_type' => 'bearer',
            'expires_in' => 5184000,
        ];
    }

    public function getInstagramUserInfo(string $accessToken): array
    {
        return [
            'id' => 'mock_app_scoped_id_456',
            'user_id' => 'mock_ig_user_123',
            'name' => 'Mock Instagram User',
            'username' => 'mock_ig_user',
        ];
    }

    public function subscribeInstagramUser(string $igUserId, string $accessToken): bool
    {
        return true;
    }

    public function sendInstagramBusinessMessage(string $igUserId, string $recipientId, string $text, string $accessToken): array
    {
        return [
            'success' => true,
            'message_id' => 'mock_ig_biz_'.Str::uuid(),
            'recipient_id' => $recipientId,
        ];
    }

    public function getPageLeadForms(string $pageId, string $pageAccessToken): array
    {
        return [
            'data' => [
                ['id' => 'mock_form_1', 'name' => 'Mock Lead Form', 'status' => 'ACTIVE', 'leads_count' => 5],
            ],
        ];
    }

    public function getFormLeads(string $formId, string $pageAccessToken): array
    {
        return [
            'data' => [
                [
                    'id' => 'mock_lead_1',
                    'created_time' => now()->toISOString(),
                    'field_data' => [
                        ['name' => 'full_name', 'values' => ['Mock Lead']],
                        ['name' => 'email', 'values' => ['mock@example.com']],
                        ['name' => 'phone_number', 'values' => ['+5511999999999']],
                    ],
                ],
            ],
        ];
    }

    public function getLeadData(string $leadgenId, string $pageAccessToken): array
    {
        return [
            'id' => $leadgenId,
            'created_time' => now()->toISOString(),
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Mock Lead']],
                ['name' => 'email', 'values' => ['mock@example.com']],
                ['name' => 'phone_number', 'values' => ['+5511999999999']],
            ],
        ];
    }

    public function getFormQuestions(string $formId, string $pageAccessToken): array
    {
        return [
            'questions' => [
                ['key' => 'full_name', 'label' => 'Full Name', 'type' => 'FULL_NAME'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'EMAIL'],
                ['key' => 'phone_number', 'label' => 'Phone Number', 'type' => 'PHONE'],
            ],
        ];
    }
}
