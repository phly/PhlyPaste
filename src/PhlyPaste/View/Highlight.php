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

        $segments = preg_split(
            '/^\#\#(.*)$/m',
            $content,
            -1,
            PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE
        );

        $count = count($segments);
        if (1 == $count) {
            return $this->highlightCode($content, $language);
        }

        if (1 == $count % 2) {
            $segments[$count - 2] .= $segments[$count - 1];
            unset($segments[$count - 1]);
        }

        $aggregate = '';
        $title     = false;
        $content   = false;
        $escaper   = $this->view->plugin('escapeHtml');
        for ($i = 0 ; $i < $count; $i += 2) {
            $lang    = $language;
            $title   = trim($segments[$i]);
            $matches = array();
            if (preg_match('/\[\s*(?P<lang>[a-zA-Z0-9_]+)\s*\]/', $title, $matches)) {
                $lang  = $matches['lang'];
                $title = preg_replace('/\[[^\]]+\]/', '', $title);
            }

            $segment = trim($segments[$i + 1]);
            $aggregate .= sprintf(
                "<h4 class=\"code title\">%s</code>\n%s",
                $escaper($title),
                $this->highlightCode($segment, $lang)
            );
        }

        return $aggregate;
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

    /**
     * Highlight code using GeSHi
     * 
     * @param  string $content 
     * @param  string $language 
     * @return string
     */
    protected function highlightCode($content, $language)
    {
        $geshi = new GeSHi($content, $language);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
        return $geshi->parse_code();
    }
}
