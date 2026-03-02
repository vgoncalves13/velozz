<?php

return [
    'title' => 'Configurações',

    // Sections
    'sections' => [
        'company_information' => 'Informação da Empresa',
        'company_information_description' => 'Configurar os detalhes e marca da sua empresa',
        'business_hours' => 'Horário de Funcionamento',
        'business_hours_description' => 'Definir o horário de atendimento ao cliente',
        'custom_fields' => 'Campos Personalizados',
        'custom_fields_description' => 'Adicionar campos personalizados aos seus leads',
        'webhooks' => 'Webhooks de Saída',
        'webhooks_description' => 'Configurar webhooks para receber notificações sobre eventos',
        'gdpr' => 'Conformidade RGPD',
        'gdpr_description' => 'Configurar retenção de dados e definições de privacidade',
        'api_access' => 'Acesso API',
        'api_access_description' => 'Gerir as suas credenciais de API',
    ],

    // Labels
    'labels' => [
        'company_name' => 'Nome da Empresa',
        'logo' => 'Logótipo',
        'primary_color' => 'Cor Primária',
        'secondary_color' => 'Cor Secundária',
        'opening_time' => 'Horário de Abertura',
        'closing_time' => 'Horário de Fecho',
        'after_hours_message' => 'Mensagem Fora de Horas',
        'webhook_url' => 'URL do Webhook',
        'events_to_send' => 'Eventos a Enviar',
        'api_key' => 'Chave API',
        'name' => 'Nome',
        'type' => 'Tipo',
        'label' => 'Etiqueta',
        'anonymize_leads' => 'Anonimizar Leads Inativos Após (meses)',
        'delete_messages' => 'Eliminar Mensagens Após (meses)',
        'consent_policy' => 'Texto da Política de Consentimento',
    ],

    // Helper texts
    'helper' => [
        'after_hours_message' => 'Mensagem enviada quando o contacto é feito fora do horário de funcionamento',
        'display_label' => 'Etiqueta de exibição (opcional, usa o nome se vazio)',
        'api_key' => 'Use esta chave para autenticar pedidos API',
        'anonymize_leads' => 'Leads não atualizados por X meses serão anonimizados',
        'delete_messages' => 'Mensagens WhatsApp com mais de X meses serão eliminadas',
        'consent_policy' => 'Texto mostrado aos utilizadores sobre consentimento de dados',
    ],

    // Field types
    'field_types' => [
        'text' => 'Texto',
        'number' => 'Número',
        'date' => 'Data',
        'boolean' => 'Sim/Não',
    ],

    // Webhook events
    'webhook_events' => [
        'lead_created' => 'Lead Criado',
        'lead_updated' => 'Lead Atualizado',
        'lead_transferred' => 'Lead Transferido',
        'message_sent' => 'Mensagem Enviada',
        'message_received' => 'Mensagem Recebida',
        'stage_changed' => 'Etapa do Funil Alterada',
        'import_completed' => 'Importação Concluída',
    ],

    // Actions
    'actions' => [
        'save_settings' => 'Guardar Configurações',
        'regenerate_api_key' => 'Regenerar Chave API',
        'add_custom_field' => 'Adicionar Campo Personalizado',
        'add_webhook' => 'Adicionar Webhook',
    ],

    // Item labels
    'item_labels' => [
        'new_field' => 'Novo Campo',
        'new_webhook' => 'Novo Webhook',
    ],

    // Placeholders
    'placeholders' => [
        'webhook_url' => 'https://seu-dominio.com/webhook',
    ],

    // Modals
    'modals' => [
        'regenerate_api_key_heading' => 'Regenerar Chave API',
        'regenerate_api_key_description' => 'Isto invalidará a sua chave API atual. Quaisquer integrações que usem a chave antiga deixarão de funcionar.',
    ],

    // Notifications
    'notifications' => [
        'settings_saved' => 'Configurações Guardadas',
        'api_key_regenerated_title' => 'Chave API Regenerada',
        'api_key_regenerated_body' => 'A sua nova chave API está agora ativa',
    ],
];
