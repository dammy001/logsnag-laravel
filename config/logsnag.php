<?php

return [
    /*
   |--------------------------------------------------------------------------
   | Logsnag.
   |--------------------------------------------------------------------------
   |
   | Configure the Logsnag options.
   |
   */

    'enabled' => env('LOGSNAG_ENABLED', false),

    'base_url' => env('LOGSNAG_BASE_URL', 'https://api.logsnag.com/v1/'),

    /**
     * The project name.
     */
    'project' => env('LOGSNAG_PROJECT'),

    /**
     * The API token.
     */
    'token' => env('LOGSNAG_TOKEN'),
];
