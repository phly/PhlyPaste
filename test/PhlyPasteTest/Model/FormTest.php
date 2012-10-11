<?php

namespace PhlyPasteTest\Model;

use PHPUnit_Framework_TestCase as TestCase;
use PhlyPaste\Model\Form;
use PhlyPaste\Model\Paste;

class FormTest extends TestCase
{
    public function expectedFields()
    {
        return array(
            array('language', 'Zend\Form\Element\Select'),
            array('private', 'Zend\Form\Element\Checkbox'),
            array('content', 'Zend\Form\Element\Textarea'),
            array('secure', 'Zend\Form\Element\Csrf'),
            array('secure', 'Zend\Form\Element\Csrf'),
            array('paste', 'Zend\Form\Element\Button'),
        );
    }

    /**
     * @dataProvider expectedFields
     */
    public function testExpectedFieldsArePresent($field, $type)
    {
        $form = Form::factory();
        $this->assertTrue($form->has($field));
        $element = $form->get($field);
        $this->assertInstanceOf($type, $element);
    }

    public function testIdIsNotInForm()
    {
        $form = Form::factory();
        $this->assertFalse($form->has('id'));
    }

    public function testBindsAPasteInstanceByDefault()
    {
        $form  = Form::factory();
        $paste = $form->getObject();
        $this->assertInstanceOf('PhlyPaste\Model\Paste', $paste);
    }

    public function testCanProvidePasteInstanceToBind()
    {
        $paste = new Paste();
        $form  = Form::factory($paste);
        $test  = $form->getObject();
        $this->assertSame($paste, $test);
    }
}
