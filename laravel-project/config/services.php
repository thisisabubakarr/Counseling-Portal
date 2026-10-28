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

    'cms' => [
        // No real CMS client exists yet — see bnu-counselor-cms-integration-architecture.md
        // Section 3. Only 'mock' is implemented; any other value is a hard error rather
        // than a silent fallback, so a misconfigured environment fails loudly.
        'client' => env('CMS_CLIENT', 'mock'),

        // RS256 public key CMS signs SSO handoff tokens with. CMS holds the private key;
        // this app only ever holds the public half. Left blank until BNU IT provides it —
        // the SSO callback refuses to run rather than accept unsigned/unverifiable tokens.
        'sso_public_key' => env('CMS_SSO_PUBLIC_KEY'),
        'sso_audience' => env('CMS_SSO_AUDIENCE', 'counselor-system'),

        // Longest remaining lifetime an arriving SSO token may still have. A CMS
        // handoff is redeemed the moment it lands, so this is deliberately tight;
        // it also bounds how long a leaked token stays usable.
        'sso_max_lifetime' => (int) env('CMS_SSO_MAX_LIFETIME', 300),

        // Expected `iss` on an arriving token. Leave unset until BNU IT confirms
        // what the CMS will send; once set, it is enforced.
        'sso_issuer' => env('CMS_SSO_ISSUER'),

        // Allows the throwaway local dev keypair to verify tokens. Must be opted
        // into explicitly AND the app must be in the local environment. Never
        // enable this anywhere a real user can reach.
        'sso_allow_dev_key' => (bool) env('CMS_SSO_ALLOW_DEV_KEY', false),
    ],

];
