<?php

return [
    // Navigation & Labels
    'label' => 'Opportunity',
    'plural' => 'Opportunities',
    'navigation' => 'Opportunities',

    // Sections
    'sections' => [
        'opportunity_information' => 'Opportunity Information',
        'value_and_stage' => 'Value & Stage',
        'additional_information' => 'Additional Information',
    ],

    // Labels
    'labels' => [
        'lead' => 'Lead',
        'product' => 'Product',
        'assigned_to' => 'Assigned To',
        'value' => 'Value',
        'stage' => 'Stage',
        'probability' => 'Probability',
        'probability_percent' => 'Probability (%)',
        'expected_close_date' => 'Expected Close Date',
        'expected_close' => 'Expected Close',
        'notes' => 'Notes',
        'loss_reason' => 'Loss Reason',
        'created' => 'Created',
    ],

    // Helper texts
    'helper' => [
        'lead' => 'Select the lead for this opportunity',
        'product' => 'Optional: Select a product for this opportunity',
        'assigned_to' => 'Assign an operator to this opportunity',
        'value' => 'Estimated revenue from this opportunity',
        'stage' => 'Current stage in the sales process',
        'probability' => 'Chance of closing (0-100%)',
        'expected_close_date' => 'When do you expect to close this deal?',
        'notes' => 'Internal notes about this opportunity',
        'loss_reason' => 'If lost, why? (Optional)',
    ],

    // Stages
    'stages' => [
        'proposal' => 'Proposal',
        'negotiation' => 'Negotiation',
        'closed_won' => 'Closed Won',
        'closed_lost' => 'Closed Lost',
    ],

    // Placeholders
    'placeholders' => [
        'not_available' => 'N/A',
        'not_set' => 'Not set',
        'unassigned' => 'Unassigned',
    ],

    // Empty states
    'empty' => [
        'title' => 'No opportunities yet',
        'description' => 'Start tracking potential sales by creating opportunities from your leads.',
    ],

    // Actions
    'actions' => [
        'create' => 'Create Opportunity',
        'view_leads' => 'View Leads',
    ],
];
