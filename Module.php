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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'PhlyPaste/MongoService' => function ($services) {
                    $config     = $services->get('config');
                    $collectionServiceName = $config['phly-paste']['mongo_collection_alias'];
                    $collection = $services->get($collectionServiceName);
                    return new Model\MongoPasteService($collection);
                },
                'PhlyPaste/MongoCollection' => function ($services) {
                    $config         = $services->get('config');
                    $dbServiceName  = $config['phly-paste']['mongo_db_alias'];
                    $collectionName = $config['phly-paste']['mongo_collection_name'];
                    $dbService      = $services->get($dbServiceName);
                    return new MongoCollection($dbService, $collectionName);
                },
                'PhlyPaste/MongoDB' => function ($services) {
                    $config           = $services->get('config');
                    $mongoServiceName = $config['phly-paste']['mongo_alias'];
                    $dbName           = $config['phly-paste']['mongo_db_name'];
                    $mongoService     = $services->get($mongoServiceName);
                    return new MongoDB($mongoService, $dbName);
                },
                'PhlyPaste/Mongo' => function ($services) {
                    $config  = $services->get('config');
                    $connOptions = $config['phly-paste']['mongo_options'];
                    return new Mongo($connOptions['server'], $connOptions['options']);
                },
            ),
        );
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'PhlyPaste\Controller\Paste' => function ($controllers) {
                    $services = $controllers->getServiceLocator();
                    $config   = $services->get('config');

                    $pasteServiceName = $config['phly-paste']['service_alias'];
                    $pasteService     = $services->get($pasteServiceName);

                    $controller = new Controller\PasteController();
                    $controller->setPasteService($pasteService);

                    return $controller;
                },
            ),
        );
    }
}
