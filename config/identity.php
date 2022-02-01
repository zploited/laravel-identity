<?php
return [
    'tenant' => [
        'identifier' => env('IDENTITY_TENANT_IDENTIFIER')
    ],
    'client' => [
        'id' => env('IDENTITY_CLIENT_ID'),
        'secret' => env('IDENTITY_CLIENT_SECRET'),
        'algorithm' => env('IDENTITY_CLIENT_ALGORITHM', 'rs256')    // algorithm used for signing access tokens for this client (currently only rs256 is supported)
    ]
];