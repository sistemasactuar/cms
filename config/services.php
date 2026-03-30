<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'odin' => [
        'base_url' => env('ODIN_BASE_URL', 'https://odin.selsacloud.com/linix/v7/38eb463e-cf8a-4c31-ab2e-eb18674726ed'),
        'empresa_uuid' => env('ODIN_EMPRESA_UUID', '38eb463e-cf8a-4c31-ab2e-eb18674726ed'),
        'realm' => env('ODIN_REALM'),
        'client_id' => env('ODIN_CLIENT_ID'),
        'client_secret' => env('ODIN_CLIENT_SECRET'),
        'verify_ssl' => filter_var(env('ODIN_VERIFY_SSL', false), FILTER_VALIDATE_BOOLEAN),
        'workflow_funcionalidad_vinculacion' => env('ODIN_WORKFLOW_FUNCIONALIDAD_VINCULACION', '6'),
    ],


];
