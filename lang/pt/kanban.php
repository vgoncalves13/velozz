<?php

return [
    // Page
    'title' => 'Quadro Kanban',
    'navigation' => 'Quadro Kanban',

    // Widget
    'widget' => [
        'pipeline_funnel' => 'Funil do Pipeline',
        'leads' => 'Leads',
        'no_stage' => 'Sem Etapa',
    ],

    // Actions
    'actions' => [
        'view' => 'Ver',
        'edit' => 'Editar',
        'create_stage' => 'Criar Etapa do Funil',
    ],

    // Labels
    'labels' => [
        'sla' => 'SLA',
        'drop_here' => 'Soltar leads aqui',
        'copy_email' => 'Copiar email',
        'email_copied' => 'Copiado!',
        'move_to_stage' => 'Mover para etapa',
    ],

    // Empty states
    'empty' => [
        'no_stages_title' => 'Ainda não há etapas do funil',
        'no_stages_description' => 'Crie a sua primeira etapa do funil para começar a organizar leads num fluxo visual.',
    ],

    // Notifications
    'notifications' => [
        'moved_title' => 'Lead movido com sucesso',
        'moved_body' => 'Movido para :stage',
    ],

    // Activities
    'activities' => [
        'stage_changed' => 'Etapa alterada de :old_stage para :new_stage',
    ],
];
