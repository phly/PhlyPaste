<?php
namespace PhlyPaste\Model;

use Zend\Authentication\AuthenticationService;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Element\Captcha as CaptchaElement;
use Zend\Form\Element\Csrf;

class Form
{
    protected $auth;
    protected $captcha;

    public function __construct(CaptchaAdapter $captcha, AuthenticationService $auth = null)
    {
        $this->captcha = $captcha;
        $this->auth    = $auth;
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

        // Add CAPTCHA if we don't have an authentication service and/or the 
        // authentication service doesn't have a current identity.
        if (!$this->auth || !$this->auth->hasIdentity()) {
            $captcha = new CaptchaElement('captcha');
            $captcha->setCaptcha($this->captcha);
            $form->add($captcha);
        }

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
