<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Logto Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint of your Logto instance.
    | For Logto Cloud: https://your-tenant.logto.app
    | For self-hosted: https://your-domain.com
    |
    */
    'endpoint' => env('LOGTO_ENDPOINT', 'https://your-logto.app'),

    /*
    |--------------------------------------------------------------------------
    | Application Credentials
    |--------------------------------------------------------------------------
    |
    | The application ID and secret from Logto Console.
    | You can find these in your application details page.
    |
    */
    'app_id' => env('LOGTO_APP_ID'),
    'app_secret' => env('LOGTO_APP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URIs
    |--------------------------------------------------------------------------
    |
    | The URIs where Logto will redirect users after authentication.
    | Make sure these are configured in your Logto application settings.
    |
    */
    'redirect_uri' => env('LOGTO_REDIRECT_URI', env('APP_URL') . '/api/v1/passport/auth/logto/callback'),
    'post_logout_redirect_uri' => env('LOGTO_POST_LOGOUT_REDIRECT_URI', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | The scopes to request from Logto.
    | Available scopes: openid, profile, email, phone, address, custom_data,
    | identities, roles, urn:logto:scope:organizations, etc.
    |
    */
    'scopes' => [
        'openid',
        'profile',
        'email',
        'phone',
        'offline_access', // For refresh token support
        'roles', // For role-based access control
    ],

    /*
    |--------------------------------------------------------------------------
    | API Resources
    |--------------------------------------------------------------------------
    |
    | The API resources that your application needs to access.
    | These should be configured in your Logto Console.
    |
    */
    'resources' => [
        env('APP_URL') . '/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Storage
    |--------------------------------------------------------------------------
    |
    | The storage driver for Logto session data.
    | Options: 'session' (default), 'cache', 'database'
    |
    */
    'storage' => env('LOGTO_STORAGE', 'session'),

    /*
    |--------------------------------------------------------------------------
    | User Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configure how users are synchronized between Logto and local database.
    |
    */
    'user_sync' => [
        // Auto-create local user on first login
        'auto_create' => env('LOGTO_AUTO_CREATE_USER', true),
        
        // Update local user info on each login
        'auto_update' => env('LOGTO_AUTO_UPDATE_USER', true),
        
        // Default user attributes
        'defaults' => [
            'transfer_enable' => 0,
            'u' => 0,
            'd' => 0,
            'balance' => 0,
            'commission_balance' => 0,
            'expired_at' => null,
        ],
    ],
];
