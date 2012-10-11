<?php
namespace PhlyPaste\Model;

use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Element\Csrf;

abstract class Form
{
    public static function factory(Paste $paste = null)
    {
        if (null === $paste) {
            $paste = new Paste();
        }

        $builder = new AnnotationBuilder();
        $form    = $builder->createForm($paste);

        $form->add(new Csrf('secure'));
        $form->add(array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'paste',
            'options' => array(
                'value' => 'Paste',
            ),
        ));

        $form->bind($paste);

        return $form;
    }
}
