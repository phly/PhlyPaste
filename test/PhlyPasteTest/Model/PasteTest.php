<?php

namespace PhlyPasteTest\Model;

use PHPUnit_Framework_TestCase as TestCase;
use PhlyPaste\Model\Paste;

class PasteTest extends TestCase
{
    public function setUp()
    {
        $this->paste = new Paste();
    }

    /**
     * @see http://pastie.org/help#adv
     */
    public function testSectionHeadingShouldCreateTitle()
    {
        $this->markTestIncomplete('Likely this should be in a view helper');
    }

    /**
     * @see http://pastie.org/help#adv
     */
    public function testSectionHeadingCanAlsoIndicateLanguage()
    {
        $this->markTestIncomplete('Likely this should be in a view helper');
    }

    /**
     * @see http://pastie.org/help#adv
     */
    public function testMultipleSectionHeadingsAreRenderedAsMultiplePasteBoxes()
    {
        $this->markTestIncomplete('Likely this should be in a view helper');
    }
}
