<?php

return [
    // Page
    'title' => 'Import Leads',
    'navigation' => 'Import Leads',

    // Actions
    'actions' => [
        'start_import' => 'Start Import',
        'fetch_data' => 'Fetch Data',
    ],

    // Wizard Steps
    'steps' => [
        'source' => 'Source',
        'source_description' => 'Choose import source',
        'mapping' => 'Mapping',
        'mapping_description' => 'Map columns to Lead fields',
        'settings' => 'Settings',
        'settings_description' => 'Configure import options',
    ],

    // Import Source
    'source' => [
        'label' => 'Import Source',
        'file' => 'Upload File (.xlsx, .csv)',
        'file_description' => 'Upload an Excel or CSV file from your computer',
        'google_sheets' => 'Google Sheets URL',
        'google_sheets_description' => 'Import directly from a published Google Sheet',
    ],

    // File Upload
    'file' => [
        'label' => 'File',
        'helper' => 'Accepted formats: .xlsx, .xls, .csv (max 10MB)',
    ],

    // Google Sheets
    'google_sheets' => [
        'label' => 'Google Sheets URL',
        'placeholder' => 'https://docs.google.com/spreadsheets/d/...',
        'how_to_title' => 'How to make your sheet public:',
        'step_1' => 'Open your Google Sheet',
        'step_2' => 'Click "File" → "Share" → "Publish to web"',
        'step_3' => 'Choose the sheet and click "Publish"',
        'step_4' => 'Copy the URL and paste it here',
    ],

    // Mapping
    'mapping' => [
        'section_title' => 'Column Mapping',
        'section_description' => 'Select which Lead field each column should map to',
        'column_label' => 'Column: ":header"',
        'helper' => 'Select which Lead field this column should map to',
        'no_file' => 'Please upload a file first',
    ],

    // Available Fields
    'fields' => [
        'ignore' => '-- Ignore this column --',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phones' => 'Phone',
        'whatsapps' => 'WhatsApp',
        'street_type' => 'Street Type',
        'street_name' => 'Street Name',
        'number' => 'Number',
        'complement' => 'Complement',
        'district' => 'District',
        'neighborhood' => 'Neighborhood',
        'region' => 'Region',
        'city' => 'City',
        'postal_code' => 'Postal Code',
        'country' => 'Country',
        'tags' => 'Tags',
        'notes' => 'Notes',
        'custom_field' => 'Custom Field',
    ],

    // Settings
    'settings' => [
        'deduplication_rules' => 'Deduplication Rules',
        'dedup_email' => 'Email',
        'dedup_email_description' => 'Skip if lead with same email exists',
        'dedup_phone' => 'Phone',
        'dedup_phone_description' => 'Skip if lead with same phone exists',
        'dedup_whatsapp' => 'WhatsApp',
        'dedup_whatsapp_description' => 'Skip if lead with same WhatsApp exists',
        'assign_operator' => 'Assign to operator',
        'assign_operator_helper' => 'Leave empty to not assign',
        'tags_label' => 'Tags',
        'tags_placeholder' => 'Add tags',
        'tags_helper' => 'Will be added to all imported leads',
    ],

    // Notifications
    'notifications' => [
        'file_processed_title' => 'File processed!',
        'file_processed_body' => 'Found :columns columns and :rows rows. Proceed to mapping step.',
        'sheets_fetched_title' => 'Google Sheets fetched!',
        'sheets_fetched_body' => 'Found :columns columns and :rows rows. Proceed to mapping step.',
        'import_started_title' => 'Import started!',
        'import_started_body' => 'Your leads are being imported. Check history below.',
        'error_reading_file' => 'Error reading file',
        'error_fetching_sheets' => 'Error fetching Google Sheets',
        'url_required_title' => 'URL required',
        'url_required_body' => 'Please enter a Google Sheets URL',
        'error_title' => 'Error',
        'fetch_first_body' => 'Please fetch the Google Sheets data first.',
        'file_not_found_title' => 'File not found',
        'file_not_found_body' => 'The uploaded file could not be found.',
    ],

    // Errors
    'errors' => [
        'unable_to_read' => 'Unable to read uploaded file. Please try again.',
        'no_data' => 'The uploaded file has no data',
        'invalid_url' => 'Invalid Google Sheets URL. Please check the URL and try again.',
        'download_failed' => 'Failed to download Google Sheets. Make sure the sheet is published as public.',
        'sheets_no_data' => 'The Google Sheet has no data',
    ],
];
