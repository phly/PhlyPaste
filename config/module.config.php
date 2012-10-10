<?php
return array(
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
                    'show' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => ':paste',
                            'constraints' => array(
                                'paste' => '[a-f0-9]{8}',
                            ),
                            'defaults' => array(
                                'action' => 'show',
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
