<?php

return [
    'navigation' => 'Meta (Instagram/Facebook)',
    'title' => 'Configurações Meta',

    'actions' => [
        'add_account' => 'Adicionar Conta',
        'connect_facebook' => 'Conectar com Facebook',
    ],

    'oauth' => [
        'success' => ':count conta(s) conectada(s) com sucesso.',
        'denied' => 'A conexão foi negada.',
        'invalid_state' => 'A sessão de ligação expirou. Por favor, tente novamente.',
    ],

    'form' => [
        'type' => 'Tipo de Canal',
        'page_id' => 'ID da Página',
        'page_id_helper' => 'O ID numérico da sua Página do Facebook ou Conta Empresarial do Instagram.',
        'page_name' => 'Nome da Página',
        'instagram_user_id' => 'ID do Utilizador Instagram',
        'instagram_user_id_helper' => 'Obrigatório para mensagens diretas do Instagram. Encontrado nas configurações da Conta Empresarial do Instagram.',
        'access_token' => 'Token de Acesso da Página',
        'access_token_helper' => 'Um Token de Acesso de Página de longa duração do Meta for Developers.',
    ],

    'webhook' => [
        'title' => 'URL do Webhook',
        'description' => 'Configure este URL no Meta App Dashboard em Webhooks. Subscreva o campo "messages" para Páginas e Instagram.',
        'verify_token_hint' => 'Token de Verificação',
    ],

    'accounts' => [
        'title' => 'Contas Conectadas',
        'empty' => 'Nenhuma conta conectada. Clique em "Adicionar Conta" para conectar Instagram ou Facebook Messenger.',
        'disconnect' => 'Desconectar',
        'disconnect_confirm' => 'Tem a certeza que pretende desconectar esta conta?',
        'delete' => 'Eliminar',
        'delete_confirm' => 'Tem a certeza que pretende eliminar esta conta? Esta ação não pode ser revertida.',
    ],

    'notifications' => [
        'invalid_token_title' => 'Token Inválido',
        'invalid_token_body' => 'O token de acesso não pôde ser validado. Por favor, verifique as suas credenciais.',
        'account_connected_title' => 'Conta Conectada',
        'account_disconnected_title' => 'Conta Desconectada',
        'account_deleted_title' => 'Conta Eliminada',
    ],
];
