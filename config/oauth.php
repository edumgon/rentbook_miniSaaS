<?php

/**
 * OAuth Configuration
 * 
 * Configure your Google OAuth credentials in the .env file.
 * Get these from: https://console.cloud.google.com/apis/credentials
 */

return [
    'google' => [
        'client_id' => Env::get('GOOGLE_CLIENT_ID', ''),
        'client_secret' => Env::get('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri' => Env::get('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/callback')
    ]
];
