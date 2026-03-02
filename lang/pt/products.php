<?php

return [
    // Navigation & Labels
    'label' => 'Produto',
    'plural' => 'Produtos',
    'navigation' => 'Produtos',

    // Sections
    'sections' => [
        'basic_information' => 'Informação Básica',
        'pricing' => 'Preços',
        'image' => 'Imagem',
    ],

    // Labels
    'labels' => [
        'name' => 'Nome',
        'title' => 'Título',
        'category' => 'Categoria',
        'description' => 'Descrição',
        'price' => 'Preço',
        'currency' => 'Moeda',
        'unit' => 'Unidade',
        'status' => 'Estado',
        'image' => 'Imagem',
        'product_image' => 'Imagem do Produto',
        'created' => 'Criado',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Nome interno do produto para referência',
        'title' => 'Título de exibição (deixe vazio para usar o nome)',
        'category' => 'Categoria do produto para organização e filtragem',
        'description' => 'Descrição detalhada do produto visível para a sua equipa',
        'price' => 'Preço base por unidade',
        'currency' => 'Moeda para preços',
        'unit' => 'Unidade de medida',
        'status' => 'Produtos ativos estão disponíveis para criar oportunidades',
        'image' => 'Imagem opcional do produto (máx 2MB)',
    ],

    // Placeholders
    'placeholders' => [
        'unit' => 'peça, hora, kg, etc.',
        'not_available' => 'N/D',
    ],

    // Categories
    'categories' => [
        'service' => 'Serviço',
        'product' => 'Produto',
        'subscription' => 'Subscrição',
        'consultation' => 'Consultoria',
    ],

    // Currencies
    'currencies' => [
        'eur' => 'EUR (€)',
        'usd' => 'USD ($)',
        'gbp' => 'GBP (£)',
    ],

    // Status
    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],

    // Empty state
    'empty' => [
        'title' => 'Ainda não há produtos',
        'description' => 'Crie o seu catálogo de produtos para começar a rastrear oportunidades e gerar receita.',
    ],

    // Actions
    'actions' => [
        'create' => 'Criar Produto',
    ],
];
