<?php

return [
    // Page title
    'title' => 'Inbox',

    // Actions
    'actions' => [
        'new_conversation' => 'New Conversation',
        'send' => 'Send',
        'assume_conversation' => 'Assume Conversation',
        'transfer_conversation' => 'Transfer Conversation',
        'add_note' => 'Add Note',
        'view_leads' => 'View Leads',
    ],

    // Labels
    'labels' => [
        'conversations' => 'Conversations',
        'select_lead' => 'Select Lead',
        'search_placeholder' => 'Search by name, email, phone or WhatsApp number',
        'no_messages' => 'No messages',
        'no_whatsapp' => 'No WhatsApp',
        'type_message' => 'Type a message...',
        'internal_note' => 'Internal Note',
        'internal_note_placeholder' => 'Add an internal note (not sent to customer)...',
        'image_caption' => 'Add a caption (optional)...',
        'document_caption' => 'Add a caption (optional)...',
        'transfer_to' => 'Transfer to',
        'assigned_to' => 'Assigned to:',
        'unknown' => 'Unknown',
        'assume' => 'Assume',
        'transfer' => 'Transfer',
        'send' => 'Send',
        'cancel' => 'Cancel',
        'image_ready' => 'Image ready to send',
        'document_ready' => 'Document ready to send',
        'transfer_conversation' => 'Transfer Conversation',
        'select_operator' => 'Select Operator',
        'select_operator_placeholder' => '-- Select Operator --',
        'no_messages_yet' => 'No messages yet',
    ],

    // Empty states
    'empty_states' => [
        'no_conversations_title' => 'No conversations yet',
        'no_conversations_description' => 'Get started by sending a message to a lead.',
        'select_conversation_title' => 'Select a conversation',
        'select_conversation_description' => 'Choose a conversation from the list to start messaging.',
    ],

    // Errors
    'errors' => [
        'cannot_send_opted_out' => 'Cannot send message. Lead has opted out or is marked as do not contact.',
        'cannot_send_image_opted_out' => 'Cannot send image. Lead has opted out or is marked as do not contact.',
        'cannot_send_document_opted_out' => 'Cannot send document. Lead has opted out or is marked as do not contact.',
    ],

    // Activities
    'activities' => [
        'internal_note_added' => 'Internal note added',
        'conversation_assumed' => 'Conversation assumed by :name',
        'conversation_transferred' => 'Conversation transferred to :name',
    ],

    // Messages
    'messages' => [
        'sent_by_you' => 'You',
        'sent_by' => 'Sent by :name',
    ],
];
