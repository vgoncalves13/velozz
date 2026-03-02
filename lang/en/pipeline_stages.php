<?php

return [
    // Navigation & Labels
    'label' => 'Pipeline Stage',
    'plural' => 'Pipeline Stages',
    'navigation' => 'Pipeline Stages',

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'automations' => 'Automations',
        'entry_automation' => 'Entry Automation',
        'entry_automation_description' => 'Triggered when a lead enters this stage',
        'exit_automation' => 'Exit Automation',
        'exit_automation_description' => 'Triggered when a lead exits this stage',
    ],

    // Labels
    'labels' => [
        'name' => 'Name',
        'color' => 'Color',
        'order' => 'Order',
        'icon' => 'Icon',
        'sla_hours' => 'SLA Hours',
        'sla' => 'SLA',
        'leads_count' => 'Leads',
        'created_at' => 'Created At',
        'template_id' => 'WhatsApp Template',
        'operador_id' => 'Assign to Operator',
        'tags' => 'Add Tags',
        'webhook_url' => 'Webhook URL',
        'hours' => 'hours',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Example: New Lead, Contacted, Proposal Sent, Negotiation, Won, Lost',
        'color' => 'Used to identify the stage on the Kanban board',
        'order' => 'Position in the pipeline (lower numbers appear first)',
        'icon' => 'Icon displayed on the Kanban board',
        'sla_hours' => 'Maximum time (in hours) that a lead should remain in this stage',
        'template_id' => 'Template sent automatically when lead enters this stage',
        'operador_id' => 'Operator automatically assigned when lead enters this stage',
        'tags' => 'Tags automatically added when lead enters this stage',
        'webhook_url' => 'URL called when lead exits this stage',
    ],

    // Icon options
    'icons' => [
        'inbox' => 'Inbox',
        'phone' => 'Phone',
        'chat' => 'Chat',
        'currency_dollar' => 'Dollar Sign',
        'check_circle' => 'Check Circle',
        'x_circle' => 'X Circle',
        'clock' => 'Clock',
        'star' => 'Star',
        'flag' => 'Flag',
    ],

    // Placeholders
    'placeholders' => [
        'webhook_url' => 'https://your-domain.com/webhook',
    ],

    // Actions
    'actions' => [
        'create' => 'Create Pipeline Stage',
        'edit' => 'Edit Pipeline Stage',
        'delete' => 'Delete Pipeline Stage',
    ],

    // Notifications
    'notifications' => [
        'created' => 'Pipeline stage created successfully',
        'updated' => 'Pipeline stage updated successfully',
        'deleted' => 'Pipeline stage deleted successfully',
    ],
];
