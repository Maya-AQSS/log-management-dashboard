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
        'archived_match' => 'This log matches an entry in archived history (same application, error code, severity and message).',
    ],

    'table' => [
        'application' => 'Application',
        'severity' => 'Severity',
        'message' => 'Message',
        'error_code' => 'Error code',
        'created_at' => 'Created at',
        'status' => 'Status',
        'url_tutorial' => 'URL Tutorial',
    ],

    'status' => [
        'archived' => 'Archived',
        'resolved' => 'Resolved',
        'unresolved' => 'Unresolved',
    ],

    'filters' => [
        'search' => 'Search',
        'search_placeholder' => 'e.g. error message',
        'severity' => 'Severity',

        'date_range' => 'Date range',
        'date_from' => 'Start date',
        'date_to' => 'End date',
        'date_range_invalid' => 'End date cannot be earlier than start date.',

        'application' => 'Application',
        'application_all' => 'All applications',

        'archived' => 'Archived',
        'archived_all' => 'All',
        'archived_archived' => 'Archived',
        'archived_not_archived' => 'Not archived',

        'resolved' => 'Resolved',
        'resolved_group' => 'Resolved / Unresolved',
        'resolved_all' => 'All',
        'resolved_resolved' => 'Resolved',
        'resolved_unresolved' => 'Unresolved',
    ],

    'buttons' => [
        'back' => 'Back',
        'apply' => 'Apply',
        'reset' => 'Reset',
        'archive' => 'Save to history',
        'view_archived' => 'View archived',
        'solved' => 'Resolve',
    ],
];
