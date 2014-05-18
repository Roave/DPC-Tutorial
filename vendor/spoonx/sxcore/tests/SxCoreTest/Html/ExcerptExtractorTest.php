<?php

namespace SxCoreTest\Html;

use PHPUnit_Framework_TestCase;
use SebastianBergmann\Exporter\Exception;
use SxCore\Html\ExcerptExtractor;

class ExcerptExtractorTest extends PHPUnit_Framework_TestCase
{

    protected $markup = '';

    public function setUp()
    {
        $paragraphs = json_decode(file_get_contents('https://baconipsum.com/api/?type=all-meat&paras=3&start-with-lorem=1'));

        foreach ($paragraphs as $paragraph) {
            $this->markup .= "<p>$paragraph</p>";
        }
    }

    public function testConstruct()
    {
        $excerptExtractor = new ExcerptExtractor;

        $this->assertInstanceOf('\SxCore\Html\ExcerptExtractor', $excerptExtractor);

        $excerptExtractor = new ExcerptExtractor($this->markup);

        $this->assertInstanceOf('\SxCore\Html\ExcerptExtractor', $excerptExtractor);
    }

    /**
     * @expectedException \SxCore\Html\Exception\InvalidArgumentException
     */
    public function testConstructFails()
    {
        new ExcerptExtractor(true);
    }

    /**
     * @expectedException \SxCore\Html\Exception\InvalidArgumentException
     */
    public function testSetMarkupFails()
    {
        $excerptExtractor = new ExcerptExtractor;

        $excerptExtractor->setMarkup(true);
    }

    public function testGetExcerpt()
    {
        $excerptExtractor = new ExcerptExtractor($this->markup);
        $excerpt          = $excerptExtractor->getExcerpt(10);

        $this->assertEquals(21, strlen($excerpt)); // 10 characters + 3 dots + paragraph open (3) and close (4) + newline

        $excerptExtractor->setMarkup($this->markup);

        $excerpt = $excerptExtractor->getExcerpt(890);
        $count   = substr_count($excerpt, '<p>');

        if ($count === 2) {
            $this->assertEquals(909, strlen($excerpt)); // 890 characters + 3 dots + (2 * paragraph open (3) and close (4)) + (2 * newline)
        } elseif ($count === 3) {
            $this->assertEquals(916, strlen($excerpt)); // 890 characters + 3 dots + (3 * paragraph open (3) and close (4)) + (2 * newline)
        }

        $excerptExtractor->setMarkup($this->markup);

        $excerpt = $excerptExtractor->getExcerpt(10000);

        $this->assertTrue(strlen($excerpt) > strlen($this->markup) && strlen($excerpt) <= strlen($this->markup) + 5); // Max line endings.
    }

    /**
     * @expectedException \SxCore\Html\Exception\RuntimeException
     */
    public function testGetExcerptWrappedFails()
    {
        $excerptExtractor = new ExcerptExtractor('<div>' . $this->markup . '</div>');

        $excerptExtractor->getExcerpt(10);
    }
}
