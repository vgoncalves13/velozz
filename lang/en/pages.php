<?php

return [
    'inbox' => [
        'title' => 'Inbox',
        'description' => 'Manage your WhatsApp conversations',
        'navigation' => 'Inbox',
    ],
    'kanban' => [
        'title' => 'Pipeline',
        'description' => 'Manage leads through your sales pipeline',
        'navigation' => 'Pipeline',
    ],
    'whatsapp_config' => [
        'title' => 'WhatsApp Configuration',
        'description' => 'Configure your WhatsApp instances',
        'navigation' => 'WhatsApp Config',
    ],
    'choose_plan' => [
        'title' => 'Choose Your Plan',
        'description' => 'Select a subscription plan',
        'navigation' => 'Choose Plan',
        'most_popular' => 'MOST POPULAR',
        'per_month' => '/month',
        'trial_days' => ':days days free trial',
        'leads_per_month' => 'leads/month',
        'messages_per_day' => 'messages/day',
        'operators' => 'operator|operators',
        'whatsapp_instances' => 'WhatsApp instance|WhatsApp instances',
        'choose_button' => 'Choose :name',
        'test_mode' => [
            'title' => 'Test Mode Enabled',
            'description' => 'This is Stripe Test Mode. Use test card: :card',
            'note' => 'No real charges will be made. Any future expiry date and any 3-digit CVC will work.',
        ],
    ],
    'tenant_settings' => [
        'title' => 'Settings',
        'description' => 'Manage your tenant settings',
        'navigation' => 'Settings',
    ],
    'import_leads' => [
        'title' => 'Import Leads',
        'description' => 'Import leads from file or URL',
        'navigation' => 'Import Leads',
    ],
];
