<?php

return [
    // Sections
    'sections' => [
        'basic_information' => 'Informação Básica',
        'role_access' => 'Função e Acesso',
    ],

    // Helper texts
    'helper' => [
        'email' => 'Um e-mail de convite será enviado para este endereço',
        'role' => 'Admin Cliente: Acesso total | Supervisor: Gerir equipa | Operador: Gerir leads | Financeiro: Ver relatórios',
        'status' => 'Convidado: Utilizador receberá e-mail para definir palavra-passe | Ativo: Utilizador pode fazer login',
    ],

    // Role options
    'role' => [
        'admin_client' => 'Admin Cliente',
        'supervisor' => 'Supervisor',
        'operator' => 'Operador',
        'financial' => 'Financeiro',
    ],

    // Status options
    'status' => [
        'active' => 'Ativo',
        'invited' => 'Convidado',
        'suspended' => 'Suspenso',
        'temporary' => 'Temporário',
    ],

    // Actions
    'actions' => [
        'send_invite' => 'Enviar Convite',
        'suspend' => 'Suspender',
        'activate' => 'Ativar',
    ],

    // Labels
    'labels' => [
        'email_address' => 'Endereço de E-mail',
        'added' => 'Adicionado',
        'never' => 'Nunca',
    ],

    // Notifications
    'notifications' => [
        'invitation_sent_title' => 'Convite enviado!',
        'invitation_sent_body' => 'E-mail de convite enviado para :email',
    ],

    // Messages
    'messages' => [
        'email_copied' => 'E-mail copiado!',
    ],
];
