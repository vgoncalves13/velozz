<?php

return [
    // Page
    'title' => 'Configuração WhatsApp',
    'navigation' => 'Config WhatsApp',

    // Actions
    'actions' => [
        'create_instance' => 'Criar Instância',
        'connect' => 'Conectar WhatsApp',
        'disconnect' => 'Desconectar',
        'check_status' => 'Verificar Estado',
    ],

    // Form Labels
    'form' => [
        'instance_id' => 'ID da Instância',
        'instance_id_helper' => 'O seu ID de instância Z-API',
        'token' => 'Token',
        'token_helper' => 'O seu token Z-API',
    ],

    // Labels
    'labels' => [
        'connection_status' => 'Estado da Ligação',
        'status' => 'Estado',
        'phone' => 'Telefone',
        'last_connected' => 'Última Ligação',
        'instance_id' => 'ID da Instância',
        'scan_qr_code' => '📱 Digitalizar Código QR',
        'how_to_connect' => '📋 Como Conectar',
        'connected_success' => '✅ Conectado com Sucesso!',
    ],

    // Status values
    'status' => [
        'connected' => 'Conectado',
        'connecting' => 'Conectando',
        'disconnected' => 'Desconectado',
        'error' => 'Erro',
    ],

    // Empty state
    'empty' => [
        'title' => 'Sem instância WhatsApp',
        'description' => 'Comece por criar uma instância WhatsApp.',
    ],

    // Instructions
    'instructions' => [
        'qr_code' => 'Abra o WhatsApp no seu telefone → Definições → Dispositivos Associados → Associar Dispositivo',
        'after_scan' => 'Depois de digitalizar, clique no botão "Verificar Estado" acima',
        'step_1' => 'Clique no botão "Conectar WhatsApp" acima',
        'step_2' => 'Digitalize o código QR com o seu WhatsApp',
        'step_3' => 'Clique em "Verificar Estado" para confirmar a ligação',
        'step_4' => 'Comece a enviar mensagens!',
        'success_message' => 'O seu WhatsApp está conectado e pronto para enviar mensagens aos seus leads. Agora pode usar templates e enviar mensagens a partir da página de detalhes do lead.',
    ],

    // Notifications
    'notifications' => [
        'instance_created_title' => 'Instância criada!',
        'instance_created_body' => 'Agora pode conectar o seu WhatsApp',
        'qr_generated_title' => 'Código QR gerado!',
        'qr_generated_body' => 'Digitalize o código QR com o seu WhatsApp',
        'disconnected_title' => 'Desconectado',
        'connected_title' => 'Conectado!',
        'connected_body' => 'Telefone: :phone',
        'not_connected_title' => 'Ainda não conectado',
    ],
];
