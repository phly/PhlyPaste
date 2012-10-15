<?php

namespace PhlyPaste\View;

use GeSHi;
use Zend\View\Helper\AbstractHelper;

class Highlight extends AbstractHelper
{
    public function __invoke($content, $language)
    {
        if ($language == 'markdown') {
            $renderer = $this->getView();
            $markdown = $renderer->plugin('markdown');
            return $markdown($content);
        }

        $geshi = new GeSHi($content, $language);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
        return $geshi->parse_code();
    }
}
