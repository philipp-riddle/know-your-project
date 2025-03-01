<?php

namespace App\Tests\Unit\Service\Helper;

use App\Service\Helper\HTMLParser;
use PHPUnit\Framework\TestCase;

class HTMLParserTest extends TestCase
{
    public function testExtractPageMeta_hasAllTags()
    {
        $html = '
            <html>
                <head>
                    <title>Page Title</title>
                    <meta name="description" content="Page Description">
                    <link rel="icon" href="favicon.ico">
                    <meta property="og:image" content="cover-image.jpg">
                </head>
                <body>
                    <p>Paragraph 1</p>
                </body>
            </html>
        ';
        $meta = HTMLParser::extractPageMeta($html);

        $this->assertSame([
            'description' => 'Page Description',
            'og:image' => 'cover-image.jpg',
            'title' => 'Page Title',
            'icon' => 'favicon.ico',
        ], $meta);
    }

    public function testExtractPageMeta_hasNoIcon()
    {
        $html = '
            <html>
                <head>
                    <title>Page Title</title>
                    <meta name="description" content="Page Description">
                </head>
                <body>
                    <p>Paragraph 1</p>
                </body>
            </html>
        ';
        $meta = HTMLParser::extractPageMeta($html);

        $this->assertSame([
            'description' => 'Page Description',
            'title' => 'Page Title',
        ], $meta);
    }

    public function testExtractPageMeta_emptyHTML()
    {
        $html = '';
        $meta = HTMLParser::extractPageMeta($html);

        $this->assertSame([], $meta);
    }

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

    public function testExtractAllText_oneTag()
    {
        $html = '<h1>Heading 1</h1>';
        $text = HTMLParser::extractAllText($html);
        $expectedText = [
            ['Heading 1', 'h1'],
        ];

        $this->assertSame($expectedText, $text);
    }

    public function testExtractAllText_multipleTags()
    {
        $html = '<h1>Heading 1</h1><p>Paragraph 2</p><li>Random list item</li>';
        $text = HTMLParser::extractAllText($html);
        $expectedText = [
            ['Heading 1', 'h1'],
            ['Paragraph 2', 'p'],
            ['Random list item', 'li'],
        ];

        $this->assertSame($expectedText, $text);
    }

    public function testExtractAllText_nestedTextInListItem()
    {
        $html = '
            <p>Paragraph 2</p>
            <ul>
                <li>Random list item</li>
            </ul>
        ';
        $text = HTMLParser::extractAllText($html);
        $expectedText = [
            ['Paragraph 2', 'p'],
            ['Random list item', 'li'],
        ];

        $this->assertSame($expectedText, $text);
    }

    public function testExtractAllText_noTextTags()
    {
        $html = '<div>Div content</div>';
        $text = HTMLParser::extractAllText($html);

        $this->assertNull($text);
    }

    public function testExtractAllText_hasTextChildNodes()
    {
        $html = '<div><li><p>Paragraph 1<p></li></div>';
        $text = HTMLParser::extractAllText($html);
        $expectedText = [
            ['Paragraph 1', 'p'],
        ];

        $this->assertSame($expectedText, $text);
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