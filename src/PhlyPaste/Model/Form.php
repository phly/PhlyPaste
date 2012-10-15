<?php
namespace PhlyPaste\Model;

use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Element\Captcha as CaptchaElement;
use Zend\Form\Element\Csrf;

class Form
{
    protected $captcha;

    public function __construct(CaptchaAdapter $captcha)
    {
        $this->captcha = $captcha;
    }

    public function factory(Paste $paste = null)
    {
        if (null === $paste) {
            $paste = new Paste();
        }

        if (null === $paste->timestamp) {
            $paste->timestamp = $_SERVER['REQUEST_TIME'];
        }

        $builder = new AnnotationBuilder();
        $form    = $builder->createForm($paste);

        $captcha = new CaptchaElement('captcha');
        $captcha->setCaptcha($this->captcha);
        $form->add($captcha);

        $form->add(new Csrf('secure'));
        $form->add(array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'paste',
            'options' => array(
                'label' => 'Paste',
            ),
            'attributes' => array(
                'type'  => 'submit',
                'class' => 'btn btn-primary',
            ),
        ));

        $form->bind($paste);

        return $form;
    }
}
