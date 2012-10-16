<?php

namespace PhlyPaste;

use Mongo;
use MongoCollection;
use MongoDB;
use Zend\Captcha\Factory as CaptchaFactory;

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
        return array('factories' => array(
            'PhlyPaste\CaptchaService' => function ($services) {
                $config = $services->get('config');
                return CaptchaFactory::factory($config['phly_paste']['captcha']);
            },
            'PhlyPaste\FormFactory' => function ($services) {
                $captcha = $services->get('PhlyPaste\Captcha');
                return new Model\Form($captcha);
            },
            'PhlyPaste\PasteTable' => function ($services) {
                $config = $services->get('config');
                $config = $config['phly_paste']['table_gateway'];
                $adapter = $services->get('PhlyPaste\DbAdapter');
                return new Model\PasteTable($adapter, $config['table']);
            },
            'PhlyPaste\TableGatewayService' => function ($services) {
                $table = $services->get('PhlyPaste\TableGateway');
                return new Model\TableGatewayPasteService($table);
            },
        ));
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'PhlyPaste\Controller\Paste' => function ($controllers) {
                    $services     = $controllers->getServiceLocator();
                    $formFactory  = $services->get('PhlyPaste\FormFactory');
                    $pasteService = $services->get('PhlyPaste\PasteService');

                    $controller = new Controller\PasteController();
                    $controller->setFormFactory($formFactory);
                    $controller->setPasteService($pasteService);

                    return $controller;
                },
            ),
        );
    }
}
