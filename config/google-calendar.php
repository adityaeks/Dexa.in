<?php

return [
    /*
     * Path to the json file containing the credentials of your Google Service Account.
     */
    'service_account_credentials_json' => storage_path('app/google-calendar/service-account-credentials.json'),

    /*
     * The id of the Google Calendar that will be used by default.
     */
    'calendar_id' => env('GOOGLE_CALENDAR_ID'),

    /*
     * The email address of the user you want to impersonate.
     */
    'user_to_impersonate' => env('GOOGLE_CALENDAR_IMPERSONATE_USER_EMAIL'),

    /*
     * The cache store that will be used to store the access token.
     */
    'cache_store' => env('GOOGLE_CALENDAR_CACHE_STORE', 'default'),

    /*
     * The cache prefix that will be used when storing the access token.
     */
    'cache_prefix' => env('GOOGLE_CALENDAR_CACHE_PREFIX', 'google_calendar_'),

    /*
     * The cache ttl that will be used when storing the access token.
     */
    'cache_ttl' => env('GOOGLE_CALENDAR_CACHE_TTL', 3600),

    /*
     * The authentication profile to use.
     * Supported: "service_account", "oauth"
     */
    'auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'service_account'),

    /*
     * Path to the json file containing the OAuth2 credentials.
     */
    'oauth_credentials_json' => storage_path('app/google-calendar/oauth-credentials.json'),

    /*
     * Path to the json file containing the OAuth2 token.
     */
    'oauth_token_json' => storage_path('app/google-calendar/oauth-token.json'),
];
