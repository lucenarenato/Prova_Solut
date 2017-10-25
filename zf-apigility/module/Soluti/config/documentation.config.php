<?php
return [
    'Soluti\\V1\\Rest\\Inserir\\Controller' => [
        'collection' => [
            'GET' => [
                'description' => 'teste',
                'response' => '{
   "_links": {
       "self": {
           "href": "/inserir"
       },
       "first": {
           "href": "/inserir?page={page}"
       },
       "prev": {
           "href": "/inserir?page={page}"
       },
       "next": {
           "href": "/inserir?page={page}"
       },
       "last": {
           "href": "/inserir?page={page}"
       }
   }
   "_embedded": {
       "inserir": [
           {
               "_links": {
                   "self": {
                       "href": "/inserir[/:inserir_id]"
                   }
               }
              "Inserir": ""
           }
       ]
   }
}',
            ],
            'POST' => [
                'description' => 'test pos',
                'request' => '{
   "Inserir": ""
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/inserir[/:inserir_id]"
       }
   }
   "Inserir": ""
}',
            ],
        ],
    ],
];
