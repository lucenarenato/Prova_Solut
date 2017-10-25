<?php

/**
 * This is the config file for ZfrCors. Just drop this file into your config/autoload folder (don't
 * forget to remove the .dist extension from the file), and configure it as you want
 */

return [
    'zfr_cors' => [
         /**
          * Set the list of allowed origins domain with protocol.
          */
         // 'allowed_origins' => ['http://example.com'],

         /**
          * Set the list of HTTP verbs.
          */
         // 'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

         /**
          * Set the list of headers. This is returned in the preflight request to indicate
          * which HTTP headers can be used when making the actual request
          */
         // 'allowed_headers' => ['Authorization'],

         /**
          * Set the max age of the preflight request in seconds. A non-zero max age means
          * that the preflight will be cached during this amount of time
          */
         // 'max_age' => 120,

         /**
          * Set the list of exposed headers. This is a whitelist that authorize the browser
          * to access to some headers using the getResponseHeader() JavaScript method. Please
          * note that this feature is buggy and some browsers do not implement it correctly
          */
         // 'exposed_headers' => [],

         /**
          * Standard CORS requests do not send or set any cookies by default. For this to work,
          * the client must set the XMLHttpRequest's "withCredentials" property to "true". For
          * this to work, you must set this option to true so that the server can serve
          * the proper response header.
          */
         // 'allowed_credentials' => false,
         // Insira o endereço de origem no array abaixo
        'allowed_origins' => ['http://localhost/', 'chrome-extension://fhbjgbiflinjbdggehcddcbncdddomop'],
          // Insira aqui os domínios que poderão fazer requisições à API e os métodos que poderão ser utilizados
        //'allowed_origins' => ['http://example.com'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        // Insira os cabeçalhos permitidos nas requisições conforme abaixo
        'allowed_headers' => ['Authorization', 'Content-Type', 'Access-Control-Allow-Origin'],
        // Outras configurações
        'max_age' => 120,
        'exposed_headers' => [],
        'allowed_credentials' => false,
    ],
];
