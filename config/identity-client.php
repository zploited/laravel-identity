<?php
return [
    'identity' => [
        'identifier' => env('IDENTITY_IDENTIFIER'),
        'client_id' => env('IDENTITY_CLIENT_ID'),
        'client_secret' => env('IDENTITY_CLIENT_SECRET'),
        'redirect_uri' => env('IDENTITY_REDIRECT_URI'),
        'scopes' => explode(' ', env('IDENTITY_SCOPE', '')),
        'protocol' => env('IDENTITY_PROTOCOL', 'https')
    ],
    'guard' => [
        'issuer' => env('IDENTITY_IDENTIFIER')
    ]
];