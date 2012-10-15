<?php

namespace PhlyPaste\View;

use Zend\View\Helper\AbstractHelper;

class TimeAgo extends AbstractHelper
{
    public function __invoke($timestamp)
    {
        if (!is_int($timestamp) && !is_numeric($timestamp)) {
            return '';
        }

        $time = $_SERVER['REQUEST_TIME'] - $timestamp;

        $tokens = array (
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
            1        => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) {
                continue;
            }
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }
    }
}
