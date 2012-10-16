<?php

namespace PhlyPasteTest\Model;

use PHPUnit_Framework_TestCase as TestCase;
use PhlyPaste\Model\Form;
use PhlyPaste\Model\Paste;
use Zend\Captcha\Dumb as DumbCaptcha;

class FormTest extends TestCase
{
    public function setUp()
    {
        $this->captcha = new DumbCaptcha();
        $this->factory = new Form($this->captcha);
    }

    public function expectedFields()
    {
        return array(
            array('language', 'Zend\Form\Element\Select'),
            array('private', 'Zend\Form\Element\Checkbox'),
            array('content', 'Zend\Form\Element\Textarea'),
            array('secure', 'Zend\Form\Element\Csrf'),
            array('captcha', 'Zend\Form\Element\Captcha'),
            array('paste', 'Zend\Form\Element\Button'),
        );
    }

    /**
     * @dataProvider expectedFields
     */
    public function testExpectedFieldsArePresent($field, $type)
    {
        $form = $this->factory->factory();
        $this->assertTrue($form->has($field));
        $element = $form->get($field);
        $this->assertInstanceOf($type, $element);
    }

    public function testIdIsNotInForm()
    {
        $form = $this->factory->factory();
        $this->assertFalse($form->has('id'));
    }

    public function testBindsAPasteInstanceByDefault()
    {
        $form  = $this->factory->factory();
        $paste = $form->getObject();
        $this->assertInstanceOf('PhlyPaste\Model\Paste', $paste);
    }

    public function testCanProvidePasteInstanceToBind()
    {
        $paste = new Paste();
        $form  = $this->factory->factory($paste);
        $test  = $form->getObject();
        $this->assertSame($paste, $test);
    }
}
