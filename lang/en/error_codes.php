<?php

return [
    'menu' => 'Error Codes',
    'title' => 'Error Codes',
    'welcome' => 'Welcome to the error codes',
    'create_title' => 'Create Error Code',
    'create_subtitle' => 'Complete the fields to register a new error code',
    'edit_title' => 'Edit Error Code',
    'edit_subtitle' => 'Update the fields for the error code',
    'detail_subtitle' => 'Press edit to update this error code',
    'empty' => 'No error codes to display',
    'deleted' => 'Error Code deleted successfully',
    'created' => 'Error Code created successfully',
    'updated' => 'Error Code updated successfully',

    'table' => [
        'application' => 'Application',
        'code' => 'Error code',
        'severity' => 'Severity',
        'name' => 'Name',
        'description' => 'Description',
        'file' => 'File',
        'line' => 'Line',
        'actions' => 'Actions',
    ],

    'buttons' => [
        'apply' => 'Apply',
        'reset' => 'Reset',
        'back' => 'Back',
        'create' => '+ New Error Code',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
    ],

    'form' => [
        'application' => 'Application',
    ],

    'messages' => [
        'delete_confirm' => 'Are you sure you want to delete this Error Code?',
    ],

    'filters' => [
        'severity' => 'Severity',
        'search' => 'Search',
        'search_placeholder' => 'Search by code or name',
        'app' => 'App',
        'app_all' => 'All applications',
    ],

    'validation' => [
        'application_id_required' => 'Application is required.',
        'application_id_invalid' => 'Selected application is invalid.',
        'code_required' => 'Error code is required.',
        'code_max' => 'Error code may not be greater than 50 characters.',
        'code_unique' => 'An error code with this ID already exists for this application.',
        'name_required' => 'Name is required.',
        'name_max' => 'Name may not be greater than 200 characters.',
        'description_max' => 'Description may not be greater than 5000 characters.',
        'file_max' => 'File may not be greater than 255 characters.',
        'line_integer' => 'Line must be an integer.',
        'line_min' => 'Line must be greater than 0.',
        'severity_invalid' => 'Selected severity is invalid.',
    ],
];
