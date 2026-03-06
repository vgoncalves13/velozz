<?php

return [
    // Page
    'title' => 'Opportunity Kanban',
    'navigation' => 'Opportunity Kanban',

    // Actions
    'actions' => [
        'edit' => 'Edit',
        'create_stage' => 'Create Opportunity Stage',
    ],

    // Labels
    'labels' => [
        'sla' => 'SLA',
        'drop_here' => 'Drop opportunities here',
        'move_to_stage' => 'Move to stage',
    ],

    // Empty states
    'empty' => [
        'no_stages_title' => 'No opportunity stages yet',
        'no_stages_description' => 'Create your first opportunity stage to start managing your sales pipeline visually.',
    ],

    // Notifications
    'notifications' => [
        'moved_title' => 'Opportunity moved successfully',
        'moved_body' => 'Moved to :stage',
    ],
];
