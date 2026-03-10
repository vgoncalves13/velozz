<?php

return [
    'navigation' => 'Meta (Instagram/Facebook)',
    'title' => 'Meta Account Settings',

    'actions' => [
        'add_account' => 'Add Account',
        'connect_facebook' => 'Connect with Facebook',
        'connect_instagram' => 'Connect with Instagram',
    ],

    'oauth' => [
        'success' => ':count account(s) connected successfully.',
        'denied' => 'The connection was denied.',
        'invalid_state' => 'The connection session expired. Please try again.',
        'instagram_success' => 'Instagram account connected successfully.',
    ],

    'form' => [
        'type' => 'Channel Type',
        'page_id' => 'Page ID',
        'page_id_helper' => 'The numeric ID of your Facebook Page or Instagram Business Account.',
        'page_name' => 'Page Name',
        'instagram_user_id' => 'Instagram User ID',
        'instagram_user_id_helper' => 'Required for Instagram Direct messages. Found in your Instagram Business Account settings.',
        'access_token' => 'Page Access Token',
        'access_token_helper' => 'A long-lived Page Access Token from Meta for Developers.',
    ],

    'webhook' => [
        'title' => 'Webhook URL',
        'description' => 'Configure this URL in your Meta App Dashboard under Webhooks. Subscribe to the "messages" field for Pages and Instagram.',
        'verify_token_hint' => 'Verify Token',
    ],

    'accounts' => [
        'title' => 'Connected Accounts',
        'empty' => 'No accounts connected yet. Click "Add Account" to connect Instagram or Facebook Messenger.',
        'disconnect' => 'Disconnect',
        'disconnect_confirm' => 'Are you sure you want to disconnect this account?',
        'delete' => 'Delete',
        'delete_confirm' => 'Are you sure you want to delete this account? This cannot be undone.',
    ],

    'lead_forms' => [
        'title' => 'Lead Ads Forms',
        'loading' => 'Loading forms...',
        'empty' => 'No forms found for this page.',
        'leads' => 'leads',
        'last_sync' => 'Last sync',
        'sync_now' => 'Sync now',
        'sync_started' => 'Sync started.',
        'subscribed' => 'Form ":name" activated. Existing leads are being imported.',
        'unsubscribed' => 'Form ":name" deactivated.',
    ],

    'notifications' => [
        'invalid_token_title' => 'Invalid Token',
        'invalid_token_body' => 'The access token could not be validated. Please check your credentials.',
        'account_connected_title' => 'Account Connected',
        'account_disconnected_title' => 'Account Disconnected',
        'account_deleted_title' => 'Account Deleted',
    ],
];
