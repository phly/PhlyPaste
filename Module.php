<?php

namespace PhlyPaste;

use Mongo;
use MongoCollection;
use MongoDB;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'PhlyPaste\Controller\Paste' => function ($controllers) {
                    $services     = $controllers->getServiceLocator();
                    $pasteService = $services->get('PhlyPaste\PasteService');

                    $controller = new Controller\PasteController();
                    $controller->setPasteService($pasteService);

                    return $controller;
                },
            ),
        );
    }
}
