<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correios REST API Configuration
    |--------------------------------------------------------------------------
    |
    | Official Correios REST API (api.correios.com.br).
    | Credentials are stored per-store in the database.
    |
    */

    'base_url' => env('CORREIOS_BASE_URL', 'https://api.correios.com.br'),

    // Token endpoint
    'token_endpoint' => '/token/v1/autentica/cartaopostagem',

    // Price endpoint
    'price_endpoint' => '/preco/v1/nacional',

    // Delivery time endpoint
    'deadline_endpoint' => '/prazo/v1/nacional',

    // HTTP timeout in seconds
    'timeout' => 10,

    // Token cache TTL in minutes (tokens last ~1h, cache for 50min to be safe)
    'token_ttl' => 50,

    // Product codes for the REST API
    'products' => [
        'sedex' => '03220',  // SEDEX CONTRATO AG
        'pac' => '03298',  // PAC CONTRATO AG
    ],

    // Human-readable names
    'product_names' => [
        '03220' => 'SEDEX',
        '03298' => 'PAC',
    ],
];
