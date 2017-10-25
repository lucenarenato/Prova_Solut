<?php
return [
    'doctrine' => [
        'driver' => [
            'Soluti_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    0 => './module/Soluti/src/V1/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Soluti' => 'Soluti_driver',
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            \Soluti\V1\Rest\Inserir\InserirResource::class => \Soluti\V1\Rest\Inserir\InserirResourceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'soluti.rest.inserir' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/inserir[/:inserir_id]',
                    'defaults' => [
                        'controller' => 'Soluti\\V1\\Rest\\Inserir\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'soluti.rest.inserir',
        ],
    ],
    'zf-rest' => [
        'Soluti\\V1\\Rest\\Inserir\\Controller' => [
            'listener' => \Soluti\V1\Rest\Inserir\InserirResource::class,
            'route_name' => 'soluti.rest.inserir',
            'route_identifier_name' => 'inserir_id',
            'collection_name' => 'inserir',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Soluti\V1\Rest\Inserir\InserirEntity::class,
            'collection_class' => \Soluti\V1\Rest\Inserir\InserirCollection::class,
            'service_name' => 'Inserir',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'Soluti\\V1\\Rest\\Inserir\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'Soluti\\V1\\Rest\\Inserir\\Controller' => [
                0 => 'application/vnd.soluti.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'Soluti\\V1\\Rest\\Inserir\\Controller' => [
                0 => 'application/vnd.soluti.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal' => [
        'metadata_map' => [
            \Soluti\V1\Rest\Inserir\InserirEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'soluti.rest.inserir',
                'route_identifier_name' => 'inserir_id',
                'hydrator' => \Zend\Hydrator\ArraySerializable::class,
            ],
            \Soluti\V1\Rest\Inserir\InserirCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'soluti.rest.inserir',
                'route_identifier_name' => 'inserir_id',
                'is_collection' => true,
            ],
        ],
    ],
    'zf-content-validation' => [
        'Soluti\\V1\\Rest\\Inserir\\Controller' => [
            'input_filter' => 'Soluti\\V1\\Rest\\Inserir\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'Soluti\\V1\\Rest\\Inserir\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [],
                'filters' => [],
                'name' => 'Inserir',
                'type' => \Zend\InputFilter\FileInput::class,
                'allow_empty' => true,
            ],
        ],
    ],
    'zf-mvc-auth' => [
        'authorization' => [
            'Soluti\\V1\\Rest\\Inserir\\Controller' => [
                'collection' => [
                    'GET' => true,
                    'POST' => true,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ],
                'entity' => [
                    'GET' => true,
                    'POST' => false,
                    'PUT' => true,
                    'PATCH' => true,
                    'DELETE' => true,
                ],
            ],
        ],
    ],
];
