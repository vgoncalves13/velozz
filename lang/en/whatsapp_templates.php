<?php

return [
    // Navigation & Labels
    'label' => 'WhatsApp Template',
    'plural' => 'WhatsApp Templates',
    'navigation' => 'Templates',

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'basic_information_description' => 'Define the template name and message content',
        'settings_automation' => 'Settings & Automation',
        'settings_automation_description' => 'Configure when and how this template should be used',
        'available_variables' => 'Available Variables',
        'available_variables_description' => 'You can use these variables in your message content',
    ],

    // Labels
    'labels' => [
        'name' => 'Template Name',
        'content' => 'Message Content',
        'active' => 'Active',
        'trigger_on' => 'Trigger On',
        'pipeline_stage' => 'Pipeline Stage',
        'stage' => 'Stage',
        'trigger' => 'Trigger',
        'created' => 'Created',
        'status' => 'Status',
        'trigger_type' => 'Trigger Type',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Give your template a descriptive name',
        'content' => 'Use variables like {name}, {company}, {operator}, etc.',
        'active' => 'Only active templates can be used',
        'trigger_on' => 'When should this template be automatically sent?',
        'pipeline_stage' => 'Template will be sent when lead moves to this stage',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'e.g., Welcome Message',
        'content' => 'Hello {name}, welcome to {company}! Your operator {operator} will contact you soon.',
        'stage_placeholder' => '—',
    ],

    // Trigger options
    'triggers' => [
        'manual' => 'Manual Only',
        'lead_created' => 'When Lead is Created',
        'import' => 'When Lead is Imported',
        'stage' => 'When Lead Moves to Stage',
    ],

    // Trigger formatted (for table display)
    'triggers_formatted' => [
        'manual' => 'Manual',
        'lead_created' => 'On Create',
        'import' => 'On Import',
        'stage' => 'Stage Trigger',
    ],

    // Filter options
    'filters' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    // Variables info
    'variables' => [
        'name' => 'Lead\'s full name',
        'company' => 'Your company name',
        'operator' => 'Assigned operator name',
        'date' => 'Current date',
        'product' => 'Product name (if applicable)',
        'link' => 'Custom link (if applicable)',
    ],
];
