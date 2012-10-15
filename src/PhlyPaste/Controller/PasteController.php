<?php

namespace PhlyPaste\Controller;

use PhlyPaste\Model\Form;
use PhlyPaste\Model\Paste;
use PhlyPaste\Model\PasteServiceInterface;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class PasteController extends AbstractActionController
{
    protected $pastes;

    public function setPasteService(PasteServiceInterface $pastes)
    {
        $this->pastes = $pastes;
    }

    public function indexAction()
    {
        return new ViewModel(array(
            'form' => Form::factory(),
        ));
    }

    public function listAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        return new ViewModel(array(
            'page'   => $this->params()->fromQuery('page', 1),
            'pastes' => $this->pastes->fetchAll(),
            'form'   => Form::factory(),
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
            'form'  => Form::factory($paste),
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

    public function processAction()
    {
        $prg = $this->prg('phly-paste/process');

        if ($prg instanceof Response) {
$this->getEventManager()->trigger('log', $this, array(
    'message' => 'PRG redirect on POST',
));
            // returned a response to redirect us
            return $prg;
        } 

        $form = Form::factory();
        if ($prg === false) {
$this->getEventManager()->trigger('log', $this, array(
    'message' => 'PRG redirect on INVALID',
));
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
$this->getEventManager()->trigger('log', $this, array(
    'message' => 'Form invalid: ' . var_export($form->getMessages(), 1),
));
            return $viewModel;
        }

        $paste = $form->getData();
        $paste = $this->pastes->create($paste);
$this->getEventManager()->trigger('log', $this, array(
    'message' => 'Form success; paste is ' . $paste->hash,
));
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
