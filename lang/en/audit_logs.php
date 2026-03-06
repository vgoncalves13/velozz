<?php

return [
    'columns' => [
        'action' => 'Action',
        'entity' => 'Entity',
        'entity_id' => 'Entity ID',
        'user' => 'User',
        'ip_address' => 'IP Address',
        'date' => 'Date',
        'user_agent' => 'User Agent',
    ],

    'filters' => [
        'action' => 'Action',
        'entity' => 'Entity',
        'user' => 'User',
        'system_actions' => 'System Actions',
        'actions' => [
            'login' => 'Login',
            'logout' => 'Logout',
            'import' => 'Import',
            'send_message' => 'Send Message',
            'qr_code_access' => 'QR Code Access',
            'lead_transfer' => 'Lead Transfer',
            'gdpr_anonymization' => 'GDPR Anonymization',
        ],
        'entities' => [
            'user' => 'User',
            'lead' => 'Lead',
            'import' => 'Import',
            'whatsapp_message' => 'WhatsApp Message',
            'whatsapp_instance' => 'WhatsApp Instance',
        ],
    ],

    'sections' => [
        'details' => 'Audit Details',
        'previous_data' => 'Previous Data',
        'new_data' => 'New Data',
    ],

    'fields' => [
        'field' => 'Field',
        'value' => 'Value',
    ],

    'defaults' => [
        'system' => 'System',
    ],
];
