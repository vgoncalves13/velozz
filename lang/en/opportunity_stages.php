<?php

return [
    // Navigation & Labels
    'label' => 'Opportunity Stage',
    'plural' => 'Opportunity Stages',
    'navigation' => 'Opportunity Stages',

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
    ],

    // Labels
    'labels' => [
        'name' => 'Name',
        'color' => 'Color',
        'order' => 'Order',
        'icon' => 'Icon',
        'sla_hours' => 'SLA Hours',
        'sla' => 'SLA',
        'opportunities_count' => 'Opportunities',
        'created_at' => 'Created At',
        'hours' => 'hours',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Example: Proposal, Negotiation, Won, Lost',
        'color' => 'Used to identify the stage on the Opportunity Kanban',
        'order' => 'Position in the sales pipeline (lower numbers appear first)',
        'icon' => 'Icon displayed on the Opportunity Kanban',
        'sla_hours' => 'Maximum time (in hours) that an opportunity should remain in this stage',
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
        'document' => 'Document',
        'handshake' => 'Handshake',
    ],

    // Default stage names (used when seeding default stages for a new tenant)
    'defaults' => [
        'proposal' => 'Proposal',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ],

    // Actions
    'actions' => [
        'create' => 'Create Opportunity Stage',
        'edit' => 'Edit Opportunity Stage',
        'delete' => 'Delete Opportunity Stage',
    ],
];
