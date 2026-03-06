<?php

return [
    // Page
    'title' => 'Qualification Kanban',
    'navigation' => 'Qualification Kanban',

    // Widget
    'widget' => [
        'pipeline_funnel' => 'Pipeline Funnel',
        'leads' => 'Leads',
        'no_stage' => 'No Stage',
    ],

    // Actions
    'actions' => [
        'view' => 'View',
        'edit' => 'Edit',
        'create_stage' => 'Create Pipeline Stage',
    ],

    // Labels
    'labels' => [
        'sla' => 'SLA',
        'drop_here' => 'Drop leads here',
        'copy_email' => 'Copy email',
        'email_copied' => 'Copied!',
        'move_to_stage' => 'Move to stage',
    ],

    // Empty states
    'empty' => [
        'no_stages_title' => 'No pipeline stages yet',
        'no_stages_description' => 'Create your first pipeline stage to start organizing leads in a visual workflow.',
    ],

    // Notifications
    'notifications' => [
        'moved_title' => 'Lead moved successfully',
        'moved_body' => 'Moved to :stage',
    ],

    // Activities
    'activities' => [
        'stage_changed' => 'Stage changed from :old_stage to :new_stage',
    ],
];
