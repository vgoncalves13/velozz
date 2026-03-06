<?php

namespace App\Services\ZApi;

use Illuminate\Support\Str;

class ZApiMockService implements ZApiServiceInterface
{
    public function generateQrCode(string $instanceId): array
    {
        $fakeQrCode = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        return [
            'qrcode' => $fakeQrCode,
            'status' => 'waiting_qr',
            'instance_id' => $instanceId,
        ];
    }

    public function getConnectionStatus(string $instanceId): array
    {
        return [
            'status' => 'connected',
            'phone' => '+351912'.rand(100000, 999999),
            'instance_id' => $instanceId,
        ];
    }

    public function sendMessage(string $instanceId, string $phone, string $message): array
    {
        usleep(500000);

        return [
            'messageId' => 'mock_'.Str::uuid()->toString(),
            'status' => 'sent',
            'phone' => $phone,
            'timestamp' => time(),
        ];
    }

    public function sendMedia(string $instanceId, string $phone, string $mediaUrl, string $caption = ''): array
    {
        usleep(1000000);

        return [
            'messageId' => 'mock_media_'.Str::uuid()->toString(),
            'status' => 'sent',
            'phone' => $phone,
            'mediaUrl' => $mediaUrl,
            'timestamp' => time(),
        ];
    }

    public function instanceExists(string $instanceId): bool
    {
        return true;
    }

    public function disconnect(string $instanceId): array
    {
        return [
            'status' => 'disconnected',
            'instance_id' => $instanceId,
        ];
    }

    public function getChats(string $instanceId, int $page = 0, int $pageSize = 100): array
    {
        return [
            'success' => true,
            'chats' => [
                ['phone' => '351912000001', 'name' => 'Mock Contact 1', 'last_message_at' => now()->subDays(1)->timestamp],
                ['phone' => '351912000002', 'name' => 'Mock Contact 2', 'last_message_at' => now()->subDays(3)->timestamp],
            ],
        ];
    }
}
