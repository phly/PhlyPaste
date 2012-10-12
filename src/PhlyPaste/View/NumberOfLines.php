<?php

namespace PhlyPaste\View;

use Zend\View\Helper\AbstractHelper;

class NumberOfLines extends AbstractHelper
{
    public function __invoke($string)
    {
        $lineCount = 1;
        $matches   = array();
        preg_match_all("#(\r\n|\r|\n)#", $string, $matches);
        if (!empty($matches) && isset($matches[1])) {
            $lineCount = count($matches[1]);
        }
        if ($lineCount <= 1) {
            return '';
        }
        return '<span class="lines">' . $lineCount . ' lines</span>';
    }
}
