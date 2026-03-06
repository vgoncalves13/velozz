<?php

return [
    'columns' => [
        'action' => 'Ação',
        'entity' => 'Entidade',
        'entity_id' => 'ID da Entidade',
        'user' => 'Utilizador',
        'ip_address' => 'Endereço IP',
        'date' => 'Data',
        'user_agent' => 'Agente de Utilizador',
    ],

    'filters' => [
        'action' => 'Ação',
        'entity' => 'Entidade',
        'user' => 'Utilizador',
        'system_actions' => 'Ações do Sistema',
        'actions' => [
            'login' => 'Login',
            'logout' => 'Logout',
            'import' => 'Importação',
            'send_message' => 'Envio de Mensagem',
            'qr_code_access' => 'Acesso por QR Code',
            'lead_transfer' => 'Transferência de Lead',
            'gdpr_anonymization' => 'Anonimização RGPD',
        ],
        'entities' => [
            'user' => 'Utilizador',
            'lead' => 'Lead',
            'import' => 'Importação',
            'whatsapp_message' => 'Mensagem WhatsApp',
            'whatsapp_instance' => 'Instância WhatsApp',
        ],
    ],

    'sections' => [
        'details' => 'Detalhes de Auditoria',
        'previous_data' => 'Dados Anteriores',
        'new_data' => 'Novos Dados',
    ],

    'fields' => [
        'field' => 'Campo',
        'value' => 'Valor',
    ],

    'defaults' => [
        'system' => 'Sistema',
    ],
];
