<?php
return array(
    'phly_paste' => array(
        // Override this in your local configuration. The array can contain two 
        // keys, "class" and "options"; the array is passed to 
        // Zend\Captcha\Factory::factory()
        'captcha' => array(
            'class' => 'Dumb',
        ),
        'table_gateway' => array(
            'table' => 'paste',
        ),
    ),
    'service_manager' => array(
        'aliases' => array(
            // You'll want to override this in your global or local configuration, and
            // indicate which service implementation you wish to use. In doing so, the
            // object returned should implement PhlyPaste\Model\PasteServiceInterface.
            'PhlyPaste\PasteService' => 'PhlyPaste\MongoService',
            // PhlyPaste\CaptchaService is defined in PhlyPaste\Module::getServiceConfig()
            // and uses the captcha configuration from this file.
            'PhlyPaste\Captcha'      => 'PhlyPaste\CaptchaService',
            // The following are for use with the TableGatewayPasteService. The 
            // first indicates the DB Adapter service to use, and the second the
            // default TableGateway service (which is defined in the Module class
            // and which uses the module-specific configuration section earlier).
            'PhlyPaste\DbAdapter'           => 'Zend\Db\Adapter\Adapter',
            'PhlyPaste\TableGateway'        => 'PhlyPaste\PasteTable',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'highlight'     => 'PhlyPaste\View\Highlight',
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
                            'raw' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/raw',
                                    'defaults' => array(
                                        'action' => 'raw',
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
