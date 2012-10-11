<?php

namespace PhlyPaste\Controller;

use PhlyPaste\Model\Form;
use PhlyPaste\Model\Paste;
use PhlyPaste\Model\PasteServiceInterface;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;
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
        $page = $this->params()->fromQuery('page', 1);
        return new ViewModel(array(
            'page'   => $this->params()->fromQuery('page', 1),
            'pastes' => $this->pastes->fetchAll(),
            'form'   => Form::factory(),
        ));
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', false);
        if (!$id) {
            return $this->redirect()->toRoute('phly-paste');
        }
        $paste = $this->pastes->fetch($id);
        if (!$paste instanceof Paste) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel(array(
            'paste' => $paste,
            'form'  => Form::factory($paste),
        ));
    }

    public function processAction()
    {
        $prg = $this->prg('phly-paste/process');

        if ($prg instanceof Response) {
            // returned a response to redirect us
            return $prg;
        } 

        $form = Form::factory();
        if ($prg === false) {
            // this wasn't a POST request, but there were no params in the flash messenger
            // probably this is the first time the form was loaded
            $viewModel = new ViewModel(array('form' => $form));
            $viewModel->setTemplate('phly-paste/paste/form');
            return $viewModel;
        }

        // $prg is an array containing the POST params from the previous request
        $form->setData($prg);

        // ... your form processing code here
        if (!$form->isValid()) {
            $viewModel = new ViewModel(array(
                'form'  => $form,
                'error' => true,
            ));
            $viewModel->setTemplate('phly-paste/paste/form');
            return $viewModel;
        }

        $paste = $form->getData();
        $paste = $this->pastes->create($paste);
        return $this->redirect()->toRoute('phly-paste/view', array(
            'id' => $paste->id,
        ));
    }
}
