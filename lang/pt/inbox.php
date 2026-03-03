<?php

return [
    // Page title
    'title' => 'Caixa de Entrada',

    // Actions
    'actions' => [
        'new_conversation' => 'Nova Conversa',
        'send' => 'Enviar',
        'assume_conversation' => 'Assumir Conversa',
        'transfer_conversation' => 'Transferir Conversa',
        'add_note' => 'Adicionar Nota',
        'view_leads' => 'Ver Leads',
    ],

    // Labels
    'labels' => [
        'conversations' => 'Conversas',
        'select_lead' => 'Selecionar Lead',
        'search_placeholder' => 'Pesquisar por nome, e-mail, telefone ou WhatsApp',
        'no_messages' => 'Sem mensagens',
        'no_whatsapp' => 'Sem WhatsApp',
        'type_message' => 'Escrever mensagem...',
        'internal_note' => 'Nota Interna',
        'internal_note_placeholder' => 'Adicionar nota interna (não enviada ao cliente)...',
        'image_caption' => 'Adicionar legenda (opcional)...',
        'document_caption' => 'Adicionar legenda (opcional)...',
        'transfer_to' => 'Transferir para',
        'assigned_to' => 'Atribuído a:',
        'unknown' => 'Desconhecido',
        'assume' => 'Assumir',
        'transfer' => 'Transferir',
        'send' => 'Enviar',
        'cancel' => 'Cancelar',
        'image_ready' => 'Imagem pronta para enviar',
        'document_ready' => 'Documento pronto para enviar',
        'transfer_conversation' => 'Transferir Conversa',
        'select_operator' => 'Selecionar Operador',
        'select_operator_placeholder' => '-- Selecionar Operador --',
        'no_messages_yet' => 'Ainda não há mensagens',
        'view_lead' => 'Ver Lead',
        'merge' => 'Fundir',
        'merge_lead' => 'Fundir Lead',
        'merge_warning' => 'O lead selecionado será fundido neste e apagado. As conversas serão mantidas separadas por canal.',
        'select_merge_lead' => 'Selecionar Lead a Fundir',
        'confirm_merge' => 'Confirmar Fusão',
        'choose_channel_to_send' => 'Escolha o canal para iniciar a conversa:',
        'channel_whatsapp' => 'WhatsApp',
        'channel_facebook' => 'Facebook Messenger',
        'channel_instagram' => 'Instagram',
    ],

    // Empty states
    'empty_states' => [
        'no_conversations_title' => 'Ainda não há conversas',
        'no_conversations_description' => 'Comece por enviar uma mensagem a um lead.',
        'select_conversation_title' => 'Selecionar uma conversa',
        'select_conversation_description' => 'Escolha uma conversa da lista para começar a enviar mensagens.',
    ],

    // Errors
    'errors' => [
        'cannot_send_opted_out' => 'Não é possível enviar mensagem. Lead desativou ou está marcado como não contactar.',
        'cannot_send_image_opted_out' => 'Não é possível enviar imagem. Lead desativou ou está marcado como não contactar.',
        'cannot_send_document_opted_out' => 'Não é possível enviar documento. Lead desativou ou está marcado como não contactar.',
        'cannot_merge_same' => 'Não é possível fundir o lead consigo próprio.',
    ],

    // Activities
    'activities' => [
        'internal_note_added' => 'Nota interna adicionada',
        'conversation_assumed' => 'Conversa assumida por :name',
        'conversation_transferred' => 'Conversa transferida para :name',
        'lead_merged' => 'Lead :name fundido neste lead',
    ],

    // Messages
    'messages' => [
        'sent_by_you' => 'Você',
        'sent_by' => 'Enviado por :name',
    ],
];
