<?php

return [
    'menu' => 'Logs',
    'title' => 'Logs list',
    'welcome' => 'Welcome to the logs',

    'empty' => 'No logs match the applied filters',

    'archived_success' => 'Log archived successfully',
    'archived_error' => 'The log could not be archived',

    'detail' => [
        'title' => 'Log detail',
        'id' => 'ID',
        'file' => 'File',
        'line' => 'Line',
        'metadata' => 'Metadata (JSON)',
        'no_metadata' => 'No metadata',
    ],

    'table' => [
        'application' => 'Application',
        'severity' => 'Severity',
        'message' => 'Message',
        'error_code' => 'Error code',
        'created_at' => 'Created at',
        'status' => 'Status',
    ],

    'status' => [
        'archived' => 'Archived',
        'resolved' => 'Resolved',
    ],

    'filters' => [
        'search' => 'Search',
        'search_placeholder' => 'e.g. error message',
        'severity' => 'Severity',

        'archived' => 'Archived',
        'archived_all' => 'All',
        'archived_archived' => 'Archived',
        'archived_not_archived' => 'Not archived',

        'resolved' => 'Resolved',
        'resolved_all' => 'All',
        'resolved_resolved' => 'Resolved',
        'resolved_unresolved' => 'Unresolved',
    ],

    'buttons' => [
        'back' => 'Back',
        'apply' => 'Apply',
        'reset' => 'Reset',
        'archive' => 'Archive',
        'view_archived' => 'View archived',
    ],
];
