<?php

return [
    // Navigation & Labels
    'label' => 'Template WhatsApp',
    'plural' => 'Templates WhatsApp',
    'navigation' => 'Templates',

    // Sections
    'sections' => [
        'basic_information' => 'Informação Básica',
        'basic_information_description' => 'Definir o nome do template e o conteúdo da mensagem',
        'settings_automation' => 'Definições & Automação',
        'settings_automation_description' => 'Configurar quando e como este template deve ser usado',
        'available_variables' => 'Variáveis Disponíveis',
        'available_variables_description' => 'Pode usar estas variáveis no conteúdo da sua mensagem',
    ],

    // Labels
    'labels' => [
        'name' => 'Nome do Template',
        'content' => 'Conteúdo da Mensagem',
        'active' => 'Ativo',
        'trigger_on' => 'Acionar Em',
        'pipeline_stage' => 'Etapa do Funil',
        'stage' => 'Etapa',
        'trigger' => 'Acionamento',
        'created' => 'Criado',
        'status' => 'Estado',
        'trigger_type' => 'Tipo de Acionamento',
    ],

    // Helper texts
    'helper' => [
        'name' => 'Dê ao seu template um nome descritivo',
        'content' => 'Use variáveis como {name}, {company}, {operator}, etc.',
        'active' => 'Apenas templates ativos podem ser usados',
        'trigger_on' => 'Quando deve este template ser enviado automaticamente?',
        'pipeline_stage' => 'Template será enviado quando o lead entrar nesta etapa',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'ex., Mensagem de Boas-Vindas',
        'content' => 'Olá {name}, bem-vindo à {company}! O seu operador {operator} entrará em contacto em breve.',
        'stage_placeholder' => '—',
    ],

    // Trigger options
    'triggers' => [
        'manual' => 'Apenas Manual',
        'lead_created' => 'Quando Lead é Criado',
        'import' => 'Quando Lead é Importado',
        'stage' => 'Quando Lead Move para Etapa',
    ],

    // Trigger formatted (for table display)
    'triggers_formatted' => [
        'manual' => 'Manual',
        'lead_created' => 'Na Criação',
        'import' => 'Na Importação',
        'stage' => 'Acionador de Etapa',
    ],

    // Filter options
    'filters' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],

    // Variables info
    'variables' => [
        'name' => 'Nome completo do lead',
        'company' => 'Nome da sua empresa',
        'operator' => 'Nome do operador atribuído',
        'date' => 'Data atual',
        'product' => 'Nome do produto (se aplicável)',
        'link' => 'Link personalizado (se aplicável)',
    ],
];
