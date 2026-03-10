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

    public function getSenderProfile(string $senderId, string $pageAccessToken): array
    {
        $response = Http::get("{$this->baseUrl}/{$senderId}", [
            'fields' => 'name,profile_pic',
            'access_token' => $pageAccessToken,
        ]);

        if (! $response->successful()) {
            return ['name' => null, 'profile_pic' => null];
        }

        return [
            'name' => $response->json('name'),
            'profile_pic' => $response->json('profile_pic'),
        ];
    }

    public function subscribePage(string $pageId, string $pageAccessToken): bool
    {
        $response = Http::post("{$this->baseUrl}/{$pageId}/subscribed_apps", [
            'subscribed_fields' => 'messages,messaging_postbacks',
            'access_token' => $pageAccessToken,
        ]);

        if (! $response->successful()) {
            Log::error('Meta Graph API: Page subscription failed', [
                'page_id' => $pageId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->successful();
    }

    public function exchangeInstagramCode(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (! $response->successful()) {
            Log::error('Instagram OAuth: Code exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception($response->json('error_message', 'Failed to exchange Instagram authorization code'));
        }

        return $response->json();
    }

    public function getInstagramLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $shortLivedToken,
        ]);

        if (! $response->successful()) {
            Log::error('Instagram OAuth: Long-lived token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception($response->json('error.message', 'Failed to get long-lived Instagram token'));
        }

        return $response->json();
    }

    public function getInstagramUserInfo(string $accessToken): array
    {
        $response = Http::get('https://graph.instagram.com/v22.0/me', [
            'fields' => 'id,user_id,name,username',
            'access_token' => $accessToken,
        ]);

        if (! $response->successful()) {
            Log::error('Instagram OAuth: Get user info failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception($response->json('error.message', 'Failed to get Instagram user info'));
        }

        return $response->json();
    }

    public function subscribeInstagramUser(string $igUserId, string $accessToken): bool
    {
        $response = Http::post("https://graph.instagram.com/v22.0/{$igUserId}/subscribed_apps", [
            'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins,messaging_referral',
            'access_token' => $accessToken,
        ]);

        if (! $response->successful()) {
            Log::error('Instagram OAuth: User subscription failed', [
                'ig_user_id' => $igUserId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->successful();
    }

    public function sendInstagramBusinessMessage(string $igUserId, string $recipientId, string $text, string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->post("https://graph.instagram.com/v22.0/{$igUserId}/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
                'messaging_type' => 'RESPONSE',
            ]);

        if (! $response->successful()) {
            Log::error('Instagram Business API: Message send failed', [
                'ig_user_id' => $igUserId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => $response->json('error.message', $response->body())];
        }

        return array_merge(['success' => true], $response->json());
    }
}
