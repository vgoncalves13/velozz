<?php

return [
    'title' => 'Settings',

    // Sections
    'sections' => [
        'company_information' => 'Company Information',
        'company_information_description' => 'Configure your company details and branding',
        'business_hours' => 'Business Hours',
        'business_hours_description' => 'Set your customer service hours',
        'custom_fields' => 'Custom Fields',
        'custom_fields_description' => 'Add custom fields to your leads',
        'webhooks' => 'Outbound Webhooks',
        'webhooks_description' => 'Configure webhooks to receive notifications about events',
        'gdpr' => 'GDPR Compliance',
        'gdpr_description' => 'Configure data retention and privacy settings',
        'api_access' => 'API Access',
        'api_access_description' => 'Manage your API credentials',
    ],

    // Labels
    'labels' => [
        'company_name' => 'Company Name',
        'logo' => 'Logo',
        'primary_color' => 'Primary Color',
        'secondary_color' => 'Secondary Color',
        'opening_time' => 'Opening Time',
        'closing_time' => 'Closing Time',
        'after_hours_message' => 'After Hours Message',
        'webhook_url' => 'Webhook URL',
        'events_to_send' => 'Events to Send',
        'api_key' => 'API Key',
        'name' => 'Name',
        'type' => 'Type',
        'label' => 'Label',
        'anonymize_leads' => 'Anonymize Inactive Leads After (months)',
        'delete_messages' => 'Delete Messages After (months)',
        'consent_policy' => 'Consent Policy Text',
    ],

    // Helper texts
    'helper' => [
        'after_hours_message' => 'Message sent when contact is made outside business hours',
        'display_label' => 'Display label (optional, uses name if empty)',
        'api_key' => 'Use this key to authenticate API requests',
        'anonymize_leads' => 'Leads not updated for X months will be anonymized',
        'delete_messages' => 'WhatsApp messages older than X months will be deleted',
        'consent_policy' => 'Text shown to users about data consent',
    ],

    // Field types
    'field_types' => [
        'text' => 'Text',
        'number' => 'Number',
        'date' => 'Date',
        'boolean' => 'Yes/No',
    ],

    // Webhook events
    'webhook_events' => [
        'lead_created' => 'Lead Created',
        'lead_updated' => 'Lead Updated',
        'lead_transferred' => 'Lead Transferred',
        'message_sent' => 'Message Sent',
        'message_received' => 'Message Received',
        'stage_changed' => 'Pipeline Stage Changed',
        'import_completed' => 'Import Completed',
    ],

    // Actions
    'actions' => [
        'save_settings' => 'Save Settings',
        'regenerate_api_key' => 'Regenerate API Key',
        'add_custom_field' => 'Add Custom Field',
        'add_webhook' => 'Add Webhook',
    ],

    // Item labels
    'item_labels' => [
        'new_field' => 'New Field',
        'new_webhook' => 'New Webhook',
    ],

    // Placeholders
    'placeholders' => [
        'webhook_url' => 'https://your-domain.com/webhook',
    ],

    // Modals
    'modals' => [
        'regenerate_api_key_heading' => 'Regenerate API Key',
        'regenerate_api_key_description' => 'This will invalidate your current API key. Any integrations using the old key will stop working.',
    ],

    // Notifications
    'notifications' => [
        'settings_saved' => 'Settings Saved',
        'api_key_regenerated_title' => 'API Key Regenerated',
        'api_key_regenerated_body' => 'Your new API key is now active',
    ],
];
