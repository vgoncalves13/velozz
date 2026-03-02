<?php

return [
    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'contact_information' => 'Contact Information',
        'address' => 'Address',
        'lead_management' => 'Lead Management',
        'consent_privacy' => 'Consent & Privacy',
        'additional_information' => 'Additional Information',
    ],

    // Helper texts
    'helper' => [
        'email' => 'Email address for notifications and communication',
        'source' => 'How this lead was acquired',
        'phones' => 'Regular phone numbers for voice calls',
        'whatsapps' => 'WhatsApp numbers for messaging (must include country code)',
        'primary_whatsapp' => 'Select which WhatsApp number is the primary contact',
        'assigned_to' => 'Team member responsible for this lead',
        'pipeline_stage' => 'Current stage in your sales pipeline',
        'priority' => 'Urgent: immediate action required | High: contact within 24h | Medium: contact within week | Low: standard follow-up',
        'tags' => 'Add keywords for easy filtering (e.g., vip, hot-lead, follow-up)',
        'consent_status' => 'GDPR consent status for communication',
        'consent_date' => 'Date when consent was given or refused',
        'opt_out' => 'Lead requested to stop receiving communications',
        'do_not_contact' => 'Internal flag to prevent all contact (e.g., legal restriction)',
        'opt_out_reason' => 'Reason for opting out',
        'opt_out_date' => 'Date when lead opted out',
        'notes' => 'Internal notes about this lead (not visible to the lead)',
        'custom_fields' => 'Add any additional information specific to your business needs',
    ],

    // Options
    'source' => [
        'import' => 'Import',
        'manual' => 'Manual',
        'api' => 'API',
        'form' => 'Form',
    ],

    'priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'consent_status' => [
        'pending' => 'Pending',
        'granted' => 'Granted',
        'refused' => 'Refused',
    ],

    // Actions
    'actions' => [
        'add_phone_number' => 'Add Phone Number',
        'add_whatsapp_number' => 'Add WhatsApp Number',
        'add_custom_field' => 'Add Custom Field',
        'assign_to_user' => 'Assign to User',
        'change_priority' => 'Change Priority',
        'create_lead' => 'Create Lead',
        'import_leads' => 'Import Leads',
    ],

    // Labels
    'labels' => [
        'key_label' => 'Field Name',
        'value_label' => 'Value',
        'whatsapp_number' => 'WhatsApp',
        'yes' => 'Yes',
        'no' => 'No',
    ],

    // Empty state
    'empty_state' => [
        'heading' => 'No leads yet',
        'description' => 'Start building your pipeline by creating your first lead or importing from a spreadsheet.',
    ],

    // Placeholders
    'placeholders' => [
        'phone' => '+351 912 345 678',
        'tags' => 'Add tags...',
    ],
];
