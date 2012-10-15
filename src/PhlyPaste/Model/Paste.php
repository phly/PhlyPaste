<?php
namespace PhlyPaste\Model;

use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 */
class Paste
{
    /**
     * @Annotation\Exclude()
     */
    public $hash;

    /**
     * @Annotation\Required(true)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Options({"label":"Language:","value_options":{"txt":"Plain Text","javascript":"JavaScript","php":"PHP","markdown":"Markdown","xml":"XML","dosini":"INI"}})
     */
    public $language = 'txt';

    /**
     * @Annotation\Required(false)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"checked_value":"true","unchecked_value":"false","label":"Private?"})
     */
    public $private = 'false';

    /**
     * @Annotation\Required(true)
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Validator({"name":"StringLength","options":{"max":32000}})
     */
    public $content = '';

    /**
     * @Annotation\Exclude()
     * @Annotation\Type("Zend\Form\Element\Hidden")
     * @Annotation\Validator({"name":"Int"})
     */
    public $timestamp;

    /**
     * @Annotation\Exclude
     */
    public $timezone = 'UTC';
}
