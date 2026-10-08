<?php

return [
    /*
     * Solo la API necesita CORS — el resto de rutas (web, healthcheck) no las
     * consume un navegador desde otro origen.
     */
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    /*
     * Lista separada por comas en CORS_ALLOWED_ORIGINS. El default cubre los
     * puertos típicos de desarrollo local (Vite/CRA) hasta que el frontend
     * tenga una URL definitiva — ajustar ahí cuando se sepa.
     */
    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', env(
            'CORS_ALLOWED_ORIGINS',
            'http://localhost:3000,http://localhost:5173,http://127.0.0.1:3000,http://127.0.0.1:5173'
        ))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // La auth es JWT por header Authorization, no por cookies — no se
    // necesitan credentials en el navegador.
    'supports_credentials' => false,
];
