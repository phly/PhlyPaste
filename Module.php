<?php

namespace PhlyPaste;

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
                $auth    = null;
                if ($services->has('PhlyPaste\AuthService')) {
                    $auth = $services->get('PhlyPaste\AuthService');
                }
                return new Model\Form($captcha, $auth);
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
            'PhlyPaste\ArrayTokenService' => function ($services) {
                $config = $services->get('config');
                $tokens = $config['phly_paste']['tokens'];
                return new Model\ArrayTokenService($tokens);
            },
            'PhlyPaste\JsonStrategy' => function ($services) {
                $renderer = $services->get('ViewJsonRenderer');
                $strategy = new View\JsonStrategy($renderer);
                return $strategy;
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
                'PhlyPaste\Controller\Api' => function ($controllers) {
                    $services     = $controllers->getServiceLocator();
                    $formFactory  = $services->get('PhlyPaste\FormFactory');
                    $pasteService = $services->get('PhlyPaste\PasteService');
                    $tokenService = $services->get('PhlyPaste\TokenService');

                    $controller = new Controller\ApiController();
                    $controller->setFormFactory($formFactory);
                    $controller->setPasteService($pasteService);
                    $controller->setTokenService($tokenService);

                    return $controller;
                },
            ),
        );
    }
}
