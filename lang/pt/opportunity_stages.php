<?php

return [
    // Navigation & Labels
    'label' => 'Etapa de Oportunidade',
    'plural' => 'Etapas de Oportunidade',
    'navigation' => 'Etapas de Oportunidade',

    // Sections
    'sections' => [
        'basic_information' => 'Informação Básica',
    ],

    // Labels
    'labels' => [
        'name' => 'Nome',
        'color' => 'Cor',
        'order' => 'Ordem',
        'icon' => 'Ícone',
        'sla_hours' => 'Horas SLA',
        'sla' => 'SLA',
        'opportunities_count' => 'Oportunidades',
        'created_at' => 'Criado em',
        'hours' => 'horas',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Exemplo: Proposta, Negociação, Ganho, Perdido',
        'color' => 'Utilizada para identificar a etapa no Kanban de Oportunidades',
        'order' => 'Posição no funil de vendas (números menores aparecem primeiro)',
        'icon' => 'Ícone exibido no Kanban de Oportunidades',
        'sla_hours' => 'Tempo máximo (em horas) que uma oportunidade deve permanecer nesta etapa',
    ],

    // Icon options
    'icons' => [
        'inbox' => 'Caixa de Entrada',
        'phone' => 'Telefone',
        'chat' => 'Chat',
        'currency_dollar' => 'Cifrão',
        'check_circle' => 'Círculo com Visto',
        'x_circle' => 'Círculo com X',
        'clock' => 'Relógio',
        'star' => 'Estrela',
        'flag' => 'Bandeira',
        'document' => 'Documento',
        'handshake' => 'Aperto de Mão',
    ],

    // Default stage names (used when seeding default stages for a new tenant)
    'defaults' => [
        'proposal' => 'Proposta',
        'negotiation' => 'Negociação',
        'won' => 'Ganho',
        'lost' => 'Perdido',
    ],

    // Actions
    'actions' => [
        'create' => 'Criar Etapa de Oportunidade',
        'edit' => 'Editar Etapa de Oportunidade',
        'delete' => 'Eliminar Etapa de Oportunidade',
    ],
];
