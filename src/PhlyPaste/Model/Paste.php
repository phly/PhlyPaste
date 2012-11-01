<?php
namespace PhlyPaste\Model;

use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\Reflection")
 */
class Paste
{
    /**
     * @Annotation\Exclude()
     */
    public $hash;

    /**
     * @Annotation\Exclude()
     */
    public $token;

    /**
     * @Annotation\Required(true)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Options({"label":"Language:",
     *     "value_options":{
     *         "apache":"Apache Config",
     *         "diff":"Diff",
     *         "html4strict":"HTML",
     *         "ini":"INI",
     *         "javascript":"JavaScript",
     *         "jquery":"jQuery",
     *         "markdown":"Markdown",
     *         "php":"PHP",
     *         "text":"Plain Text",
     *         "python":"Python",
     *         "ruby":"Ruby",
     *         "bash":"Shell Script",
     *         "sql":"SQL",
     *         "vim":"Vim",
     *         "xml":"XML"
     *     }
     * })
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
