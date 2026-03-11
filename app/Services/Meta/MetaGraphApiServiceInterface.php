<?php

namespace App\Services\Meta;

interface MetaGraphApiServiceInterface
{
    /**
     * Send a text message via Instagram DM
     */
    public function sendInstagramMessage(string $recipientId, string $text, string $accessToken): array;

    /**
     * Send a text message via Facebook Messenger
     */
    public function sendFacebookMessage(string $pageId, string $recipientId, string $text, string $accessToken): array;

    /**
     * Send a media message (image, video, audio, document)
     */
    public function sendMedia(string $pageId, string $recipientId, string $mediaUrl, string $mediaType, string $accessToken): array;

    /**
     * Verify a webhook subscription challenge
     */
    public function verifyWebhook(string $mode, string $token, string $challenge): ?string;

    /**
     * Validate an access token and return page info
     */
    public function validateToken(string $pageId, string $accessToken): array;

    /**
     * Verify the X-Hub-Signature-256 header from Meta
     */
    public function verifySignature(string $payload, string $signature): bool;

    /**
     * Exchange a short-lived user token for a long-lived token
     *
     * @return array{access_token: string, expires_in: int}
     */
    public function extendToken(string $shortLivedToken): array;

    /**
     * Get all Facebook Pages the user manages
     *
     * @return array{data: array<int, array{id: string, name: string, access_token: string}>}
     */
    public function getPages(string $userAccessToken): array;

    /**
     * Get the Instagram Business Account ID linked to a Facebook Page
     */
    public function getInstagramBusinessAccount(string $pageId, string $pageAccessToken): ?string;

    /**
     * Subscribe a Facebook Page to receive webhook events (messages field)
     */
    public function subscribePage(string $pageId, string $pageAccessToken): bool;

    /**
     * Get the name and profile picture of a Messenger/Instagram sender
     *
     * @return array{name: string|null, profile_pic: string|null}
     */
    public function getSenderProfile(string $senderId, string $pageAccessToken, \App\Enums\Channel $channel): array;

    /**
     * Exchange an Instagram authorization code for a short-lived access token
     *
     * @return array{access_token: string, token_type: string}
     */
    public function exchangeInstagramCode(string $code, string $redirectUri): array;

    /**
     * Exchange a short-lived Instagram token for a long-lived one
     *
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function getInstagramLongLivedToken(string $shortLivedToken): array;

    /**
     * Get the Instagram user info for the authenticated user
     *
     * @return array{id: string, name: string, username?: string}
     */
    public function getInstagramUserInfo(string $accessToken): array;

    /**
     * Subscribe an Instagram user to receive webhook message events
     */
    public function subscribeInstagramUser(string $igUserId, string $accessToken): bool;

    /**
     * Send a message via Instagram Business Login (direct Instagram Graph API)
     *
     * @return array{success: bool, message_id?: string}
     */
    public function sendInstagramBusinessMessage(string $igUserId, string $recipientId, string $text, string $accessToken): array;

    /**
     * List all lead gen forms for a Facebook Page
     *
     * @return array{data: array<int, array{id: string, name: string, status: string, leads_count: int}>}
     */
    public function getPageLeadForms(string $pageId, string $pageAccessToken): array;

    /**
     * List leads already submitted in a lead gen form
     *
     * @return array{data: array<int, array{id: string, created_time: string, field_data: array<int, array{name: string, values: array<int, string>}>}>}
     */
    public function getFormLeads(string $formId, string $pageAccessToken): array;

    /**
     * Retrieve full data for a single lead by its leadgen_id
     *
     * @return array{id: string, created_time: string, field_data: array<int, array{name: string, values: array<int, string>}>}
     */
    public function getLeadData(string $leadgenId, string $pageAccessToken): array;

    /**
     * Fetch the question fields defined in a lead gen form
     *
     * @return array{questions: array<int, array{key: string, label: string, type: string}>}
     */
    public function getFormQuestions(string $formId, string $pageAccessToken): array;
}
