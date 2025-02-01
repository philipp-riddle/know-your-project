<?php

namespace App\Tests\Unit\Service\Helper;

use App\Service\Helper\HTMLParser;
use PHPUnit\Framework\TestCase;

class HTMLParserTest extends TestCase
{
    public function testExtractHeading_h1()
    {
        $html = '<h1>Heading 1</h1>';
        $heading = HTMLParser::extractHeading($html);

        $this->assertSame('Heading 1', $heading);
    }

    public function testExtractHeading_withParagraphDistraction()
    {
        $html = '<p>Paragraph for distraction</p><h2>Heading 2</h2>';
        $heading = HTMLParser::extractHeading($html);

        $this->assertSame('Heading 2', $heading);
    }

    public function testExtractHeading_h1_remove()
    {
        $html = '<h1>Heading 1</h1>';
        $heading = HTMLParser::extractHeading($html, remove: true);

        $this->assertSame('Heading 1', $heading);
        $this->assertSame('', $html);
    }

    public function testExtractHeading_h1_withParagraphDistraction_remove()
    {
        $html = '<p>Paragraph for distraction</p><h2>Heading 2</h2>';
        $heading = HTMLParser::extractHeading($html, remove: true);

        $this->assertSame('Heading 2', $heading);
        $this->assertSame('<p>Paragraph for distraction</p>', $html);
    }

    public function testExtractParagraph_p()
    {
        $html = '<p>Paragraph 1</p>';
        $paragraph = HTMLParser::extractParagraph($html);

        $this->assertSame('Paragraph 1', $paragraph);
    }

    public function testExtractParagraph_withMultipleParagraphs()
    {
        $html = '<h1>Heading 1</h1><p>Paragraph 2</p>';
        $paragraph = HTMLParser::extractParagraph($html);

        $this->assertSame('Paragraph 2', $paragraph);
    }

    public function testExtractParagraph_p_remove()
    {
        $html = '<p>Paragraph 1</p>';
        $paragraph = HTMLParser::extractParagraph($html, remove: true);

        $this->assertSame('Paragraph 1', $paragraph);
        $this->assertSame('', $html);
    }

    public function testExtractParagraph_p_withMultipleParagraphs_remove()
    {
        $html = '<h1>Heading 1</h1><p>Paragraph 2</p>';
        $paragraph = HTMLParser::extractParagraph($html, remove: true);

        $this->assertSame('Paragraph 2', $paragraph);
        $this->assertSame('<h1>Heading 1</h1>', $html);
    }

    public function testExtract_withEmptyHtml()
    {
        $html = '';
        $heading = HTMLParser::extractHeading($html);
        $paragraph = HTMLParser::extractParagraph($html);

        $this->assertNull($heading);
        $this->assertNull($paragraph);
    }
}