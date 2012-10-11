<?php
return array(
    'phly-paste' => array(
        'service_alias'          => 'PhlyPaste/MongoService',
        'mongo_collection_alias' => 'PhlyPaste/MongoCollection',
        'mongo_collection_name'  => 'pastes',
        'mongo_db_alias'         => 'PhlyPaste/MongoDB',
        'mongo_db_name'          => 'site',
        'mongo_alias'            => 'PhlyPaste/Mongo',
        'mongo_options'          => array(
            'server'  => 'mongodb://localhost:27017',
            'options' => array(
                'connect' => true,
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'PhlyPaste\Controller\Paste' => 'PhlyPaste\Controller\PasteController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'phly-paste' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/pastes[/]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'PhlyPaste\Controller',
                        'controller'    => 'Paste',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'process' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'process',
                            'defaults' => array(
                                'action' => 'process',
                            ),
                        ),
                    ),
                    'view' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => ':paste',
                            'constraints' => array(
                                'paste' => '[a-f0-9]{8}',
                            ),
                            'defaults' => array(
                                'action' => 'view',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'PhlyPaste' => __DIR__ . '/../view',
        ),
    ),
);
