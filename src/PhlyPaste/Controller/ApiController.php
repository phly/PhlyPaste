<?php

namespace PhlyPaste\Controller;

use PhlyPaste\Model\Form as FormFactory;
use PhlyPaste\Model\PasteServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class ApiController extends AbstractActionController
{
    protected $formFactory;
    protected $pastes;

    public function setFormFactory(FormFactory $factory)
    {
        $this->formFactory = $factory;
    }

    public function setPasteService(PasteServiceInterface $pastes)
    {
        $this->pastes = $pastes;
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
        $headers = $request->getHeaders();
        $accept  = $headers->get('Accept');
        if (false === $accept) {
            return $this->createUnacceptableResponse($e);
        }
        if (!$accept->match('application/json')) {
            return $this->createUnacceptableResponse($e);
        }

        return parent::onDispatch($e);
    }

    public function listAction()
    {
        $pastes = $this->pastes->fetchAll();
        $last   = count($pastes);
        $page   = $this->params()->fromQuery('page', 1);

        $links  = array(
            $this->getLink('canonical', $this->url('phly-paste/list'), $page),
            $this->getLink('self', $this->url('phly-paste/api/list'), $page),
            $this->getLink('first', $this->url('phly-paste/api/list')),
            $this->getLink('last', $this->url('phly-paste/api/list'), $last),
        );
        if ($page != 1) {
            $links[] = $this->getLink('prev', $this->url('phly-paste/api/list'), $page - 1);
        }
        if ($page != $last) {
            $links[] = $this->getLink('next', $this->url('phly-paste/api/list'), $page + 1);
        }

        $items  = array();
        foreach ($pastes as $paste) {
            $items[] = array(
                $this->getLink('canonical', $this->url('phly-paste/view', array('paste' => $paste->hash))),
                $this->getLink('item', $this->url('phly-paste/api/item', array('paste' => $paste->hash))),
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
            $this->getLink('canonical', $this->url('phly-paste/view', array('paste' => $paste->hash))),
            $this->getLink('self', $this->url('phly-paste/api/item', array('paste' => $paste->hash))),
            $this->getLink('up', $this->url('phly-paste/api/list')),
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
        // get paste from raw body
        $request = $this->getRequest();
        $json    = $request->getContent();

        // decode paste from json as assoc array
        $data = json_decode($json, true);

        // get form, and set paste data
        $form = $this->formFactory->factory();
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
        try {
            $this->pastes->create($paste);
        } catch (\Exception $e) {
            // create errors...
            return $this->createError('error-creating', $e->getMessage());
        }

        // create payload to return
        $canonical = $this->url('phly-paste/view', array('paste' => $paste->hash));
        $response  = $e->getResponse();
        $response->setStatusCode(201);

        $response->getHeaders()->addHeaderLine('Location', $canonical);
        return new JsonModel(array(
            'links' => array(
                $this->getLink('canonical', $canonical),
                $this->getLink('self', $this->url('phly-paste/api/item', array('paste' => $paste->hash))),
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
            return $this->createError('invalid-id');
            return;
        }
        return $paste;
    }

    protected function getLink($rel, $url, $page = null)
    {
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
}
