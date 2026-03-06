<?php

namespace App\Services\ZApi;

interface ZApiServiceInterface
{
    /**
     * Generate QR Code for WhatsApp connection
     */
    public function generateQrCode(string $instanceId): array;

    /**
     * Get connection status
     */
    public function getConnectionStatus(string $instanceId): array;

    /**
     * Send text message
     */
    public function sendMessage(string $instanceId, string $phone, string $message): array;

    /**
     * Send media message
     */
    public function sendMedia(string $instanceId, string $phone, string $mediaUrl, string $caption = ''): array;

    /**
     * Check if instance exists
     */
    public function instanceExists(string $instanceId): bool;

    /**
     * Disconnect instance
     */
    public function disconnect(string $instanceId): array;

    /**
     * Get list of chats (individual contacts only, excluding groups)
     *
     * @return array{success: bool, chats: array<int, array{phone: string, name: string, last_message_at: int|null}>}
     */
    public function getChats(string $instanceId, int $page = 0, int $pageSize = 100): array;
}
