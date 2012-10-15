<?php

namespace PhlyPaste\Controller;

use PhlyPaste\Model\Form as FormFactory;
use PhlyPaste\Model\Paste;
use PhlyPaste\Model\PasteServiceInterface;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class PasteController extends AbstractActionController
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

    public function indexAction()
    {
        return new ViewModel(array(
            'form' => $this->formFactory->factory(),
        ));
    }

    public function listAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        return new ViewModel(array(
            'page'   => $this->params()->fromQuery('page', 1),
            'pastes' => $this->pastes->fetchAll(),
            'form'   => $this->formFactory->factory(),
        ));
    }

    public function viewAction()
    {
        $paste = $this->getPaste();
        if ($paste instanceof Response) {
            return $paste;
        }

        return new ViewModel(array(
            'paste' => $paste,
        ));
    }

    public function embedAction()
    {
        $paste = $this->getPaste();
        if ($paste instanceof Response) {
            return $paste;
        }

        return new JsonModel(array(
            'paste' => $paste,
        ));
    }

    public function repasteAction()
    {
        $paste = $this->getPaste();
        if ($paste instanceof Response) {
            return $paste;
        }

        $viewModel = new ViewModel(array(
            'form'  => $this->formFactory->factory($paste),
        ));
        $viewModel->setTemplate('phly-paste/paste/index');
        return $viewModel;
    }


    public function rawAction()
    {
        $paste = $this->getPaste();
        if ($paste instanceof Response) {
            return $paste;
        }

        $response = $this->getResponse();
        $response->setContent($paste->content);
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain');
        return $response;
    }

    public function processAction()
    {
        $prg = $this->prg('phly-paste/process');

        if ($prg instanceof Response) {
            // returned a response to redirect us
            return $prg;
        } 

        $form = $this->formFactory->factory();
        if ($prg === false) {
            // this wasn't a POST request, but there were no params in the flash messenger
            // probably this is the first time the form was loaded
            return $this->redirect()->toRoute('phly-paste');
        }

        // $prg is an array containing the POST params from the previous request
        $form->setData($prg);

        // ... your form processing code here
        if (!$form->isValid()) {
            $viewModel = new ViewModel(array(
                'form'  => $form,
                'error' => true,
            ));
            $viewModel->setTemplate('phly-paste/paste/index');
            return $viewModel;
        }

        $paste = $form->getData();
        $paste = $this->pastes->create($paste);
        return $this->redirect()->toRoute('phly-paste/view', array(
            'paste' => $paste->hash,
        ));
    }

    protected function getPaste()
    {
        $id = $this->params()->fromRoute('paste', false);
        if (!$id) {
            return $this->redirect()->toRoute('phly-paste');
        }
        $paste = $this->pastes->fetch($id);
        if (!$paste instanceof Paste) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        return $paste;
    }
}
