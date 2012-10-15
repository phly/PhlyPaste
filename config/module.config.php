<?php
return array(
    'phly-paste' => array(
        'mongo_collection_alias' => 'PhlyPaste/MongoCollection',
        'mongo_collection_name'  => 'pastes',
        'mongo_db_name'          => 'site',
        'mongo_options'          => array(
            'server'  => 'mongodb://localhost:27017',
            'options' => array(
                'connect' => true,
            ),
        ),
    ),
    'service_manager' => array(
        'aliases' => array(
            'PhlyPaste\PasteService' => 'PhlyPaste\MongoService',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'numberoflines' => 'PhlyPaste\View\NumberOfLines',
            'timeago'       => 'PhlyPaste\View\TimeAgo',
        ),
    ),
    'router' => array(
        'routes' => array(
            'phly-paste' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/paste[/]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'PhlyPaste\Controller',
                        'controller'    => 'Paste',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
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
                        'may_terminate' => true,
                        'child_routes' => array(
                            'embed' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '.js',
                                    'defaults' => array(
                                        'action' => 'embed',
                                    ),
                                ),
                            ),
                            'repaste' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/repaste',
                                    'defaults' => array(
                                        'action' => 'repaste',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'list' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'list',
                            'defaults' => array(
                                'action' => 'list',
                            ),
                        ),
                    ),
                    'process' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'process',
                            'defaults' => array(
                                'action' => 'process',
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
