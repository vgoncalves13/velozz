<?php

return [
    // Navigation & Labels
    'label' => 'Etapa do Funil',
    'plural' => 'Etapas do Funil',
    'navigation' => 'Etapas do Funil',

    // Sections
    'sections' => [
        'basic_information' => 'Informação Básica',
        'automations' => 'Automações',
        'entry_automation' => 'Automação de Entrada',
        'entry_automation_description' => 'Acionada quando um lead entra nesta etapa',
        'exit_automation' => 'Automação de Saída',
        'exit_automation_description' => 'Acionada quando um lead sai desta etapa',
    ],

    // Labels
    'labels' => [
        'name' => 'Nome',
        'color' => 'Cor',
        'order' => 'Ordem',
        'icon' => 'Ícone',
        'sla_hours' => 'Horas SLA',
        'sla' => 'SLA',
        'leads_count' => 'Leads',
        'created_at' => 'Criado em',
        'template_id' => 'Template WhatsApp',
        'operador_id' => 'Atribuir ao Operador',
        'tags' => 'Adicionar Etiquetas',
        'webhook_url' => 'URL do Webhook',
        'hours' => 'horas',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Exemplo: Novo Lead, Contactado, Proposta Enviada, Negociação, Ganho, Perdido',
        'color' => 'Utilizada para identificar a etapa no quadro Kanban',
        'order' => 'Posição no funil (números menores aparecem primeiro)',
        'icon' => 'Ícone exibido no quadro Kanban',
        'sla_hours' => 'Tempo máximo (em horas) que um lead deve permanecer nesta etapa',
        'template_id' => 'Template enviado automaticamente quando o lead entra nesta etapa',
        'operador_id' => 'Operador atribuído automaticamente quando o lead entra nesta etapa',
        'tags' => 'Etiquetas adicionadas automaticamente quando o lead entra nesta etapa',
        'webhook_url' => 'URL chamada quando o lead sai desta etapa',
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
    ],

    // Placeholders
    'placeholders' => [
        'webhook_url' => 'https://seu-dominio.com/webhook',
    ],

    // Actions
    'actions' => [
        'create' => 'Criar Etapa do Funil',
        'edit' => 'Editar Etapa do Funil',
        'delete' => 'Eliminar Etapa do Funil',
    ],

    // Notifications
    'notifications' => [
        'created' => 'Etapa do funil criada com sucesso',
        'updated' => 'Etapa do funil atualizada com sucesso',
        'deleted' => 'Etapa do funil eliminada com sucesso',
    ],
];
