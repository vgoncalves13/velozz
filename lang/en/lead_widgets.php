<?php

return [
    'sections' => [
        'basic_info' => 'Basic Information',
        'form_builder' => 'Form Builder',
        'appearance' => 'Appearance',
        'config' => 'Configuration',
        'position_appearance' => 'Position & Appearance',
        'embed_code' => 'Embed Code',
        'preview' => 'Preview',
    ],

    'labels' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'active' => 'Active',
        'redirect_url' => 'Redirect URL',
        'fields' => 'Fields',
        'fields_count' => 'Fields',
        'field_type' => 'Field Type',
        'field_label' => 'Label',
        'field_name' => 'Field Name',
        'placeholder' => 'Placeholder',
        'required' => 'Required',
        'default_value' => 'Default Value',
        'help_text' => 'Help Text',
        'options' => 'Options',
        'option_value' => 'Value',
        'order' => 'Order',
        'validation_min' => 'Min',
        'validation_max' => 'Max',
        'validation_regex' => 'Regex',
        'width' => 'Width',
        'alignment' => 'Alignment',
        'input_size' => 'Input Size',
        'border_radius' => 'Border Radius',
        'text_color' => 'Text Color',
        'border_color' => 'Border Color',
        'background_color' => 'Background Color',
        'font_size' => 'Font Size',
        'button_text' => 'Button Text',
        'button_color' => 'Button Color',
        'button_text_color' => 'Button Text Color',
        'button_size' => 'Button Size',
        'embed_script' => 'Script Tag (recommended)',
        'embed_iframe' => 'iFrame Tag',
        'whatsapp_number' => 'WhatsApp Number',
        'auto_message' => 'Auto Message',
        'position' => 'Position',
        'animation' => 'Animation',
        'button_text_label' => 'Button Text (optional)',
        'show_text' => 'Show Text',
        'status' => 'Status',
        'created' => 'Created',
    ],

    'helpers' => [
        'slug' => 'Used in the public URL of the form. Only letters, numbers, and hyphens.',
        'redirect_url' => 'URL to redirect after form submission. Leave empty to show a success message.',
        'field_name' => 'Internal field identifier. Use snake_case (e.g., full_name).',
        'whatsapp_number' => 'International format (e.g., +351912345678).',
        'auto_message' => 'Use {{nome}} as placeholder for the contact\'s name.',
        'button_text' => 'Leave empty to show only the WhatsApp icon.',
        'order' => 'Order in which the fields will be displayed.',
    ],

    'field_types' => [
        'text' => 'Text',
        'email' => 'Email',
        'phone' => 'Phone',
        'number' => 'Number',
        'textarea' => 'Textarea',
        'select' => 'Select',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio',
        'file' => 'File Upload',
    ],

    'alignments' => [
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
    ],

    'sizes' => [
        'sm' => 'Small',
        'md' => 'Medium',
        'lg' => 'Large',
    ],

    'border_radius' => [
        'none' => 'None',
        'sm' => 'Small',
        'md' => 'Medium',
        'lg' => 'Large',
        'full' => 'Full (Circle)',
    ],

    'positions' => [
        'bottom_right' => 'Bottom Right',
        'bottom_left' => 'Bottom Left',
        'top_right' => 'Top Right',
        'top_left' => 'Top Left',
    ],

    'animations' => [
        'none' => 'None',
        'pulse' => 'Pulse',
        'bounce' => 'Bounce',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'actions' => [
        'add_field' => 'Add Field',
        'add_option' => 'Add Option',
        'open_preview' => 'Open Preview',
    ],

    'embedded_forms' => [
        'navigation' => 'Embedded Forms',
        'label' => 'Embedded Form',
        'plural' => 'Embedded Forms',
        'notifications' => [
            'created' => 'Embedded form created successfully.',
            'updated' => 'Embedded form updated successfully.',
            'deleted' => 'Embedded form deleted.',
        ],
        'empty' => [
            'title' => 'No embedded forms yet',
            'description' => 'Create your first form to embed on your website.',
        ],
        'actions' => [
            'create' => 'Create Form',
        ],
    ],

    'whatsapp_widgets' => [
        'navigation' => 'WhatsApp Widgets',
        'label' => 'WhatsApp Widget',
        'plural' => 'WhatsApp Widgets',
        'notifications' => [
            'created' => 'WhatsApp widget created successfully.',
            'updated' => 'WhatsApp widget updated successfully.',
            'deleted' => 'WhatsApp widget deleted.',
        ],
        'empty' => [
            'title' => 'No WhatsApp widgets yet',
            'description' => 'Create your first widget to add a WhatsApp button to your website.',
        ],
        'actions' => [
            'create' => 'Create Widget',
        ],
    ],
];
