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
    public $id;

    /**
     * @Annotation\Required(true)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Options({"label":"Language:","value_options":{"txt":"Plain Text","javascript":"JavaScript","php":"PHP","markdown":"Markdown","xml":"XML","dosini":"INI"}})
     */
    public $language = 'txt';

    /**
     * @Annotation\Required(true)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"checked_value":"true","unchecked_value":"false","label":"Private?"})
     */
    public $private = 'false';

    /**
     * @Annotation\Required(true)
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Filter({"name":"StringTrim"})
     */
    public $content = '';
}
