<?php

return [
    // Page
    'title' => 'WhatsApp Configuration',
    'navigation' => 'WhatsApp Config',

    // Actions
    'actions' => [
        'create_instance' => 'Create Instance',
        'connect' => 'Connect WhatsApp',
        'disconnect' => 'Disconnect',
        'check_status' => 'Check Status',
        'sync_chats' => 'Sync Conversations',
    ],

    // Form Labels
    'form' => [
        'instance_id' => 'Instance ID',
        'instance_id_helper' => 'Your Z-API instance ID',
        'token' => 'Token',
        'token_helper' => 'Your Z-API token',
        'sync_days' => 'Days to Sync',
        'sync_days_helper' => 'Import contacts who have messaged in the last X days (individual chats only, groups are excluded)',
    ],

    // Labels
    'labels' => [
        'connection_status' => 'Connection Status',
        'status' => 'Status',
        'phone' => 'Phone',
        'last_connected' => 'Last Connected',
        'instance_id' => 'Instance ID',
        'scan_qr_code' => '📱 Scan QR Code',
        'how_to_connect' => '📋 How to Connect',
        'connected_success' => '✅ Connected Successfully!',
    ],

    // Status values
    'status' => [
        'connected' => 'Connected',
        'connecting' => 'Connecting',
        'disconnected' => 'Disconnected',
        'error' => 'Error',
    ],

    // Empty state
    'empty' => [
        'title' => 'No WhatsApp instance',
        'description' => 'Get started by creating a WhatsApp instance.',
    ],

    // Instructions
    'instructions' => [
        'qr_code' => 'Open WhatsApp on your phone → Settings → Linked Devices → Link a Device',
        'after_scan' => 'After scanning, click "Check Status" button above',
        'step_1' => 'Click "Connect WhatsApp" button above',
        'step_2' => 'Scan the QR code with your WhatsApp',
        'step_3' => 'Click "Check Status" to confirm connection',
        'step_4' => 'Start sending messages!',
        'success_message' => 'Your WhatsApp is connected and ready to send messages to your leads. You can now use templates and send messages from the lead details page.',
    ],

    // Notifications
    'notifications' => [
        'instance_created_title' => 'Instance created!',
        'instance_created_body' => 'Now you can connect your WhatsApp',
        'qr_generated_title' => 'QR Code generated!',
        'qr_generated_body' => 'Scan the QR code with your WhatsApp',
        'disconnected_title' => 'Disconnected',
        'connected_title' => 'Connected!',
        'connected_body' => 'Phone: :phone',
        'not_connected_title' => 'Not connected yet',
        'sync_started_title' => 'Sync started!',
        'sync_started_body' => 'Importing contacts from the last :days days. This may take a few minutes.',
    ],
];
