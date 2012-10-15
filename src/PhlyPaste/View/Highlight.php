<?php

namespace PhlyPaste\View;

use GeSHi;
use HTMLPurifier;
use HTMLPurifier_Config;
use Zend\View\Helper\AbstractHelper;

class Highlight extends AbstractHelper
{
    public function __invoke($content, $language)
    {
        if ($language == 'markdown') {
            return $this->renderMarkdown($content);
        }

        $geshi = new GeSHi($content, $language);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
        return $geshi->parse_code();
    }

    /**
     * Renders markdown
     * 
     * @param  string $content 
     * @return string
     */
    protected function renderMarkdown($content)
    {
        $renderer  = $this->getView();
        $markdown  = $renderer->plugin('markdown');
        $dirtyHtml =  $markdown($content);
        $cleanHtml = $this->sanitiseHtml($dirtyHtml);
        return $cleanHtml;
    }

    /**
     * Sanitises HTML by running it through HTMLPurifier using default 
     * configuration.
     * 
     * @param  string $dirtyHtml 
     * @return string
     */
    protected function sanitiseHtml($dirtyHtml)
    {
        $config   = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($dirtyHtml);
    }
}
