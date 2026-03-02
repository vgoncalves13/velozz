<?php

return [
    // Navigation & Labels
    'label' => 'Oportunidade',
    'plural' => 'Oportunidades',
    'navigation' => 'Oportunidades',

    // Sections
    'sections' => [
        'opportunity_information' => 'Informação da Oportunidade',
        'value_and_stage' => 'Valor & Etapa',
        'additional_information' => 'Informação Adicional',
    ],

    // Labels
    'labels' => [
        'lead' => 'Lead',
        'product' => 'Produto',
        'assigned_to' => 'Atribuído a',
        'value' => 'Valor',
        'stage' => 'Etapa',
        'probability' => 'Probabilidade',
        'probability_percent' => 'Probabilidade (%)',
        'expected_close_date' => 'Data de Fecho Prevista',
        'expected_close' => 'Fecho Previsto',
        'notes' => 'Notas',
        'loss_reason' => 'Motivo da Perda',
        'created' => 'Criado',
    ],

    // Helper texts
    'helper' => [
        'lead' => 'Selecionar o lead para esta oportunidade',
        'product' => 'Opcional: Selecionar um produto para esta oportunidade',
        'assigned_to' => 'Atribuir um operador a esta oportunidade',
        'value' => 'Receita estimada desta oportunidade',
        'stage' => 'Etapa atual no processo de vendas',
        'probability' => 'Probabilidade de fecho (0-100%)',
        'expected_close_date' => 'Quando espera fechar este negócio?',
        'notes' => 'Notas internas sobre esta oportunidade',
        'loss_reason' => 'Se perdido, porquê? (Opcional)',
    ],

    // Stages
    'stages' => [
        'proposal' => 'Proposta',
        'negotiation' => 'Negociação',
        'closed_won' => 'Ganho',
        'closed_lost' => 'Perdido',
    ],

    // Placeholders
    'placeholders' => [
        'not_available' => 'N/D',
        'not_set' => 'Não definido',
        'unassigned' => 'Não atribuído',
    ],

    // Empty states
    'empty' => [
        'title' => 'Ainda não há oportunidades',
        'description' => 'Comece a rastrear vendas potenciais criando oportunidades a partir dos seus leads.',
    ],

    // Actions
    'actions' => [
        'create' => 'Criar Oportunidade',
        'view_leads' => 'Ver Leads',
    ],
];
