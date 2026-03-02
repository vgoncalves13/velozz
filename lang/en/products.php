<?php

return [
    // Navigation & Labels
    'label' => 'Product',
    'plural' => 'Products',
    'navigation' => 'Products',

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'pricing' => 'Pricing',
        'image' => 'Image',
    ],

    // Labels
    'labels' => [
        'name' => 'Name',
        'title' => 'Title',
        'category' => 'Category',
        'description' => 'Description',
        'price' => 'Price',
        'currency' => 'Currency',
        'unit' => 'Unit',
        'status' => 'Status',
        'image' => 'Image',
        'product_image' => 'Product Image',
        'created' => 'Created',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Internal product name for reference',
        'title' => 'Display title (leave empty to use name)',
        'category' => 'Product category for organization and filtering',
        'description' => 'Detailed product description visible to your team',
        'price' => 'Base price per unit',
        'currency' => 'Currency for pricing',
        'unit' => 'Unit of measurement',
        'status' => 'Active products are available for creating opportunities',
        'image' => 'Optional product image (max 2MB)',
    ],

    // Placeholders
    'placeholders' => [
        'unit' => 'piece, hour, kg, etc.',
        'not_available' => 'N/A',
    ],

    // Categories
    'categories' => [
        'service' => 'Service',
        'product' => 'Product',
        'subscription' => 'Subscription',
        'consultation' => 'Consultation',
    ],

    // Currencies
    'currencies' => [
        'eur' => 'EUR (€)',
        'usd' => 'USD ($)',
        'gbp' => 'GBP (£)',
    ],

    // Status
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    // Empty state
    'empty' => [
        'title' => 'No products yet',
        'description' => 'Create your product catalog to start tracking opportunities and generating revenue.',
    ],

    // Actions
    'actions' => [
        'create' => 'Create Product',
    ],
];
