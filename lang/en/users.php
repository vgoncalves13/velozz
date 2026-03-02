<?php

return [
    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'role_access' => 'Role & Access',
    ],

    // Helper texts
    'helper' => [
        'email' => 'An invitation email will be sent to this address',
        'role' => 'Admin Client: Full access | Supervisor: Manage team | Operator: Handle leads | Financial: View reports',
        'status' => 'Invited: User will receive email to set password | Active: User can login',
    ],

    // Role options
    'role' => [
        'admin_client' => 'Admin Client',
        'supervisor' => 'Supervisor',
        'operator' => 'Operator',
        'financial' => 'Financial',
    ],

    // Status options
    'status' => [
        'active' => 'Active',
        'invited' => 'Invited',
        'suspended' => 'Suspended',
        'temporary' => 'Temporary',
    ],

    // Actions
    'actions' => [
        'send_invite' => 'Send Invite',
        'suspend' => 'Suspend',
        'activate' => 'Activate',
    ],

    // Labels
    'labels' => [
        'email_address' => 'Email Address',
        'added' => 'Added',
        'never' => 'Never',
    ],

    // Notifications
    'notifications' => [
        'invitation_sent_title' => 'Invitation sent!',
        'invitation_sent_body' => 'Invitation email sent to :email',
    ],

    // Messages
    'messages' => [
        'email_copied' => 'Email copied!',
    ],
];
