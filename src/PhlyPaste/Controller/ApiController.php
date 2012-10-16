<?php

namespace PhlyPaste\Controller;

use PhlyPaste\Model\Form as FormFactory;
use PhlyPaste\Model\PasteServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;

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
        // decode paste from json as assoc array
        // get form, and set paste data
        // if form invalid, report errors
        // if form valid, attempt to create paste
        // if exception, report error
        // create payload to return
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
}
