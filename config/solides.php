<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL base da API Sólides
    |--------------------------------------------------------------------------
    */
    'base_url' => env('SOLIDES_BASE_URL', 'https://app.solides.com'),

    /*
    |--------------------------------------------------------------------------
    | Locale obrigatório na URL base
    |--------------------------------------------------------------------------
    */
    'locale' => env('SOLIDES_LOCALE', 'pt-BR'),

    /*
    |--------------------------------------------------------------------------
    | Timeout HTTP (segundos)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('SOLIDES_HTTP_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Modo agrupado (vaga inferida)
    |--------------------------------------------------------------------------
    |
    | Busca várias páginas de GET /curriculos até esgotar ou atingir o limite.
    |
    */
    'grouped_max_curriculo_pages' => (int) env('SOLIDES_GROUPED_MAX_CURRICULO_PAGES', 50),

    'grouped_http_timeout' => (int) env('SOLIDES_GROUPED_HTTP_TIMEOUT', 90),
];
