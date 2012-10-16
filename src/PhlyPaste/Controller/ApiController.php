<?php

namespace PhlyPaste\Controller;

use PhlyPaste\Model\Form as FormFactory;
use PhlyPaste\Model\Paste;
use PhlyPaste\Model\PasteServiceInterface;
use PhlyPaste\Model\TokenServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Uri\Http as HttpUri;
use Zend\View\Model\JsonModel;

class ApiController extends AbstractActionController
{
    protected $baseUri;
    protected $formFactory;
    protected $pastes;
    protected $tokenService;

    public function setFormFactory(FormFactory $factory)
    {
        $this->formFactory = $factory;
    }

    public function setPasteService(PasteServiceInterface $pastes)
    {
        $this->pastes = $pastes;
    }

    public function setTokenService(TokenServiceInterface $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function onDispatch(MvcEvent $e)
    {
        // Setup JsonStrategy
        $services = $this->getServiceLocator();
        $jsonStrategy = $services->get('ViewJsonStrategy');
        $view         = $services->get('View');
        $view->getEventManager()->attach($jsonStrategy, 100);

        // Test that we have an appropriate "accept" header
        $request = $e->getRequest();
        $accept  = $request->getHeaders('Accept', false);
        if (!$accept) {
            return $this->createUnacceptableResponse($e);
        }
        if (!$accept->match('application/json')) {
            return $this->createUnacceptableResponse($e);
        }

        return parent::onDispatch($e);
    }

    public function listAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            return $this->createAction();
        }
        if (!$request->isGet()) {
            return $this->createError('invalid-method');
        }

        $pastes = $this->pastes->fetchAll();
        $last   = count($pastes);
        $page   = $this->params()->fromQuery('page', 1);

        $links  = array(
            $this->getLink('canonical', $this->url()->fromRoute('phly-paste/list'), $page),
            $this->getLink('self', $this->url()->fromRoute('phly-paste/api'), $page),
            $this->getLink('first', $this->url()->fromRoute('phly-paste/api')),
            $this->getLink('last', $this->url()->fromRoute('phly-paste/api'), $last),
        );
        if ($page != 1) {
            $links[] = $this->getLink('prev', $this->url()->fromRoute('phly-paste/api'), $page - 1);
        }
        if ($page != $last) {
            $links[] = $this->getLink('next', $this->url()->fromRoute('phly-paste/api'), $page + 1);
        }

        $items  = array();
        foreach ($pastes as $paste) {
            $items[] = array(
                $this->getLink('canonical', $this->url()->fromRoute('phly-paste/view', array('paste' => $paste->hash))),
                $this->getLink('item', $this->url()->fromRoute('phly-paste/api/item', array('paste' => $paste->hash))),
            );
        }

        $model = new JsonModel(array(
            'links' => $links,
            'items' => $items,
        ));
        return $model;
    }

    public function fetchAction()
    {
        $paste = $this->getPaste();
        if ($paste instanceof JsonModel) {
            return $paste;
        }
        $links = array(
            $this->getLink('canonical', $this->url()->fromRoute('phly-paste/view', array('paste' => $paste->hash))),
            $this->getLink('self', $this->url()->fromRoute('phly-paste/api/item', array('paste' => $paste->hash))),
            $this->getLink('up', $this->url()->fromRoute('phly-paste/api')),
        );
        $lines = preg_split("/(\r\n|\n|\r)/", $paste->content, 2);
        $title = array_shift($lines);
        return new JsonModel(array(
            'links'     => $links,
            'title'     => $title,
            'language'  => $paste->language,
            'timestamp' => $paste->timestamp,
        ));
    }

    public function createAction()
    {
        // verify token
        $token = $this->verifyApiToken();
        if ($token instanceof JsonModel) {
            return $token;
        }

        // get paste from raw body
        $request = $this->getRequest();
        $json    = $request->getContent();

        // decode paste from json as assoc array
        $data = json_decode($json, true);

        // get form, and set paste data
        $form = $this->formFactory->factory();
        $form->setValidationGroup('language', 'private', 'content');
        $form->setData($data);

        // if form invalid, report errors
        if (!$form->isValid()) {
            // create errors...
            $model = $this->createError('invalid-paste');
            $model->setVariable('errors', $form->getMessages());
            return $model;
        }

        // if form valid, attempt to create paste
        $paste = $form->getData();
        $paste->token = $token;
        try {
            $this->pastes->create($paste);
        } catch (\Exception $e) {
            // create errors...
            return $this->createError('error-creating', $e->getMessage());
        }

        // create payload to return
        $canonical = $this->url()->fromRoute('phly-paste/view', array('paste' => $paste->hash));
        $response  = $this->getResponse();
        $response->setStatusCode(201);

        $response->getHeaders()->addHeaderLine('Location', $canonical);
        return new JsonModel(array(
            'links' => array(
                $this->getLink('canonical', $canonical),
                $this->getLink('self', $this->url()->fromRoute('phly-paste/api/item', array('paste' => $paste->hash))),
            ),
        ));
    }

    protected function getPaste()
    {
        $id = $this->params()->fromRoute('paste', false);
        if (!$id) {
            return $this->createError('missing-id');
        }
        $paste = $this->pastes->fetch($id);
        if (!$paste instanceof Paste) {
            return $this->createError('invalid-id', var_export($paste, 1));
        }
        return $paste;
    }

    protected function getLink($rel, $url, $page = null)
    {
        $baseUri = $this->getBaseUri();
        $url     = $baseUri . $url;
        if ($page !== null) {
            $url .= '?page=' . $page;
        }
        return array(
            'rel'  => $rel,
            'href' => $url,
        );
    }

    protected function createUnacceptableResponse()
    {
        $response = $this->getResponse();
        $response->setStatusCode(406);
        $model = new JsonModel(array(
            'error'   => 406,
            'message' => 'Invalid Accept type; must be application/json to access the API',
        ));
        $e->setResult($model);
        return $model;
    }

    protected function createError($error, $message = null)
    {
        $response = $this->getResponse();
        $code     = 400;
        switch ($error) {
            case 'unauthorized':
                $code    = 401;
                $message = 'Unauthorized';
                break;
            case 'invalid-method':
                $code    = 405;
                $message = 'Method not allowed';
                break;
            case 'invalid-id':
                $code    = 404;
                $message = 'Paste not found';
                break;
            case 'error-creating':
                $code = 500;
                if (null === $message) {
                    $message = 'Paste hash missing or invalid';
                }
                break;
            case 'invalid-paste':
            case 'missing-id':
            default:
                if (null === $message) {
                    $message = 'Paste hash missing or invalid';
                }
                break;
        }
        $response->setStatusCode($code);
        $model = new JsonModel(array(
            'error'   => $code,
            'message' => $message,
        ));
        return $model;
    }

    protected function verifyApiToken()
    {
        $request     = $this->getRequest();
        $tokenHeader = $request->getHeaders('X-PhlyPaste-Token', false);
        if (!$tokenHeader) {
            return $this->createError('unauthorized');
        }

        $token = $tokenHeader->getFieldValue();
        $token = trim($token);

        if (!$this->tokenService->verify($token)) {
            return $this->createError('unauthorized');
        }
        return $token;
    }

    protected function getBaseUri()
    {
        if ($this->baseUri) {
            return $this->baseUri;
        }

        $request    = $this->getRequest();
        $currentUri = $request->getUri();
        $uri = new HttpUri();
        $uri->setScheme($currentUri->getScheme());
        $uri->setHost($currentUri->getHost());
        $uri->setPort($currentUri->getPort());
        $uri->setPath($request->getBaseUrl());
        $this->baseUri = $uri->toString();
        $this->baseUri = rtrim($this->baseUri, '/');
        return $this->baseUri;
    }
}
