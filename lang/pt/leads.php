<?php

return [
    // Sections
    'sections' => [
        'basic_information' => 'Informação Básica',
        'contact_information' => 'Informação de Contacto',
        'address' => 'Morada',
        'lead_management' => 'Gestão de Lead',
        'consent_privacy' => 'Consentimento e Privacidade',
        'additional_information' => 'Informação Adicional',
    ],

    // Helper texts
    'helper' => [
        'email' => 'Endereço de e-mail para notificações e comunicação',
        'source' => 'Como este lead foi adquirido',
        'phones' => 'Números de telefone regulares para chamadas de voz',
        'whatsapps' => 'Números do WhatsApp para mensagens (deve incluir código do país)',
        'primary_whatsapp' => 'Selecione qual número do WhatsApp é o contacto principal',
        'assigned_to' => 'Membro da equipa responsável por este lead',
        'pipeline_stage' => 'Etapa atual no seu funil de vendas',
        'priority' => 'Urgente: ação imediata necessária | Alto: contactar dentro de 24h | Médio: contactar dentro da semana | Baixo: seguimento padrão',
        'tags' => 'Adicione palavras-chave para filtragem fácil (ex: vip, lead-quente, seguimento)',
        'consent_status' => 'Estado do consentimento RGPD para comunicação',
        'consent_date' => 'Data em que o consentimento foi dado ou recusado',
        'opt_out' => 'Lead solicitou parar de receber comunicações',
        'do_not_contact' => 'Sinalizador interno para impedir todo o contacto (ex: restrição legal)',
        'opt_out_reason' => 'Motivo para desativar',
        'opt_out_date' => 'Data em que o lead desativou',
        'notes' => 'Notas internas sobre este lead (não visível para o lead)',
        'custom_fields' => 'Adicione qualquer informação adicional específica das suas necessidades de negócio',
    ],

    // Options
    'source' => [
        'import' => 'Importação',
        'manual' => 'Manual',
        'api' => 'API',
        'form' => 'Formulário',
        'whatsapp' => 'WhatsApp',
        'instagram' => 'Instagram',
        'facebook_messenger' => 'Facebook Messenger',
    ],

    'priority' => [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],

    'consent_status' => [
        'pending' => 'Pendente',
        'granted' => 'Concedido',
        'refused' => 'Recusado',
    ],

    // Actions
    'actions' => [
        'add_phone_number' => 'Adicionar Número de Telefone',
        'add_whatsapp_number' => 'Adicionar Número WhatsApp',
        'add_custom_field' => 'Adicionar Campo Personalizado',
        'assign_to_user' => 'Atribuir a Utilizador',
        'change_priority' => 'Alterar Prioridade',
        'create_lead' => 'Criar Lead',
        'import_leads' => 'Importar Leads',
    ],

    // Labels
    'labels' => [
        'key_label' => 'Nome do Campo',
        'value_label' => 'Valor',
        'whatsapp_number' => 'WhatsApp',
        'yes' => 'Sim',
        'no' => 'Não',
    ],

    // Empty state
    'empty_state' => [
        'heading' => 'Ainda não há leads',
        'description' => 'Comece a construir o seu funil criando o seu primeiro lead ou importando de uma folha de cálculo.',
    ],

    // Placeholders
    'placeholders' => [
        'phone' => '+351 912 345 678',
        'tags' => 'Adicionar etiquetas...',
    ],
];
