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
        ));
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'PhlyPaste\Controller\Paste' => function ($controllers) {
                    $services     = $controllers->getServiceLocator();
                    $pasteService = $services->get('PhlyPaste\PasteService');
                    $formFactory  = $services->get('PhlyPaste\FormFactory');

                    $controller = new Controller\PasteController();
                    $controller->setPasteService($pasteService);
                    $controller->setFormFactory($formFactory);

                    return $controller;
                },
            ),
        );
    }
}
