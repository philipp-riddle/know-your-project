<?php

namespace App\Tests\Unit\Service\Helper;

use App\Service\Helper\TextMarker;
use PHPUnit\Framework\TestCase;

class TextMarkerTest extends TestCase
{
    public function testGetMarkedTextFromRichText_oneWord()
    {
        $html = '<p>This is a test text to test the TextMarker class.</p>';
        $searchTerm = 'test';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<p>This is a <mark>test</mark> text to <mark>test</mark> the TextMarker class.</p>';
        
        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_distractionTextInBetween()
    {
        $html = '
            <p>This is a test text to test the TextMarker class.</p>
            <p>Some distraction text.</p>
            <p>Another test text.</p>
        ';
        $searchTerm = 'test';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<p>This is a <mark>test</mark> text to <mark>test</mark> the TextMarker class.</p><p>Another <mark>test</mark> text.</p>';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_multipleWords()
    {
        $html = '<p>This is a test text to test the TextMarker class.</p>';
        $searchTerm = 'test text to test';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<p>This is a <mark>test text to test</mark> the TextMarker class.</p>';
        
        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_multipleWordsAndTags()
    {
        $html = '
            <h1>This is a test text to test the TextMarker class.</h1>
            <p>Another test text.</p>
        ';
        $searchTerm = 'test text';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<h1>This is a <mark>test text</mark> to test the TextMarker class.</h1>';
        $expectedMarkedText .= '<p>Another <mark>test text</mark>.</p>';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_simpleText()
    {
        $html = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'test';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<p>This is a <mark>test</mark> text to <mark>test</mark> the TextMarker class.</p>';
        
        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_url()
    {
        $html = 'https://www.example.com';
        $searchTerm = 'example';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<p>https://www.<mark>example</mark>.com</p>'; // whole link marked as it does not exceed the max length

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_inList()
    {
        $html = '
            <ul>
                <li>This is a test text to test the TextMarker class.</li>
                <li>Another test text.</li>
            </ul>
        ';
        $searchTerm = 'test';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html);
        $expectedMarkedText = '<ul><li>This is a <mark>test</mark> text to <mark>test</mark> the TextMarker class.</li></ul>';
        $expectedMarkedText .= '<ul><li>Another <mark>test</mark> text.</li></ul>';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_useOnlyParagraphsForMatchedText()
    {
        $html = '
            <h1>This is a test text to test the TextMarker class.</h1>
            <p>Another test text.</p>
        ';
        $searchTerm = 'test text';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html, defaultTagForMatchedElement: 'p');
        $expectedMarkedText = '<p>This is a <mark>test text</mark> to test the TextMarker class.</p>';
        $expectedMarkedText .= '<p>Another <mark>test text</mark>.</p>';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedTextFromRichText_textElementsInChildNodes_withStrongTags()
    {
        $html = '
            <li>
                <p>
                    <strong>Centralized Knowledge Base:</strong>
                    It advocates for the consolidation of critical information, making retrieval easy and reducing reliance on scattered documentation.
                </p>
            </li>
        ';
        $searchTerm = 'Knowledge';

        $markedText = TextMarker::getMarkedTextFromRichText($searchTerm, $html, defaultTagForMatchedElement: 'p');
        $expectedMarkedText = '<p>Centralized <mark>Knowledge</mark> Base:
                    It advocates for the consolidation of critical information, making retrieval</p>';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_oneWord()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'test';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'This is a <mark>test</mark> text to <mark>test</mark> the TextMarker class.';
        
        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_multipleWords()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'test text to test';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'This is a <mark>test text to test</mark> the TextMarker class.';
        
        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_atEndOfString()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'class';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'This is a test text to test the TextMarker <mark>class</mark>.';
        
        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_atEndOfSentence()
    {
        $text = 'This is a test string. To test the TextMarker class.';
        $searchTerm = 'string';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'This is a test <mark>string</mark>. To test the TextMarker class.';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_atStartOfSentence()
    {
        $text = 'This is a test string. Perfect test for the TextMarker class.';
        $searchTerm = 'perfect';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'This is a test string. <mark>Perfect</mark> test for the TextMarker class.';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_tokenized_singleWord()
    {
        $text = 'London is a great city!';
        $searchTerm = 'I study at the University of London';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = '<mark>London</mark> is a great city!';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_tokenized_multiWord()
    {
        $text = 'The University of London is great!';
        $searchTerm = 'I study at the University of London';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'The <mark>University</mark> of <mark>London</mark> is great!';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_notFound()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'Know Your Project';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);

        $this->assertNull($markedText);
    }

    public function testGetMarkedText_withIncludedStrongTag()
    {
        $text = '
            <strong>Centralized Knowledge Base:</strong>
            It advocates for the consolidation of critical information, making retrieval easy and reducing reliance on scattered documentation.
        ';
        $searchTerm = 'Knowledge';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = '
            <strong>Centralized <mark>Knowledge</mark> Base:</strong>
            It advocates for the consolidation of critical';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_inUrl_fullWordMatch()
    {
        $text = 'https://www.example.com';
        $searchTerm = 'example';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'https://www.<mark>example</mark>.com';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_inUrl_partialWordMatch()
    {
        $text = 'https://www.example.com';
        $searchTerm = 'exam';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'https://www.<mark>example</mark>.com';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMarkedText_inUrl_multipleWordsWithDots()
    {
        $text = 'https://www.example.com';
        $searchTerm = 'www.example';

        $markedText = TextMarker::getMarkedText($searchTerm, $text);
        $expectedMarkedText = 'https://<mark>www.example</mark>.com';

        $this->assertSame($expectedMarkedText, $markedText);
    }

    public function testGetMatches_inUrl_multipleWordsWithDots()
    {
        $text = 'https://www.example.com';
        $searchTerm = 'www.example';

        $matches = TextMarker::getMatches($searchTerm, $text);
        $expectedMatches = [
            [
                'text' => 'www.example',
                'index' => 8,
            ],
        ];

        $this->assertSame($expectedMatches, $matches);
    }

    public function testGetMatches_oneWord()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'test';

        $matches = TextMarker::getMatches($searchTerm, $text);
        $expectedMatches = [
            [
                'text' => 'test',
                'index' => 10,
            ],
            [
                'text' => 'test',
                'index' => 23,
            ],
        ];
        
        $this->assertSame($expectedMatches, $matches);
    }

    public function testGetMatches_mustIgnoreSpaces()
    {
        $text = 'THIS IS A TEST.';
        $searchTerm = 'test';

        $matches = TextMarker::getMatches($searchTerm, $text);
        $expectedMatches = [
            [
                'text' => 'TEST',
                'index' => 10,
            ],
        ];
        
        $this->assertSame($expectedMatches, $matches);
    }

    public function testGetMatches_onlyPartial()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'est';

        $matches = TextMarker::getMatches($searchTerm, $text);
        $expectedMatches = [
            [
                'text' => 'test',
                'index' => 10,
            ],
            [
                'text' => 'test',
                'index' => 23,
            ],
        ];
        
        $this->assertSame($expectedMatches, $matches);
    }

    public function testGetMatches_multipleWords()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'test text to test';

        $matches = TextMarker::getMatches($searchTerm, $text);
        $expectedMatches = [
            [
                'text' => 'test text to test',
                'index' => 10,
            ],
        ];
        
        $this->assertSame($expectedMatches, $matches);
    }

    public function testGetMatches_empty()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $searchTerm = 'Know Your Project';

        $matches = TextMarker::getMatches($searchTerm, $text);

        $this->assertEmpty($matches);
    }

    public function testCutWord_inMiddleOfString()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $startIndex = 10;

        $cutText = TextMarker::cutWord($text, $startIndex);
        $expectedCutText = 'test';
        
        $this->assertSame($expectedCutText, $cutText);
    }

    public function testCutWord_multipleWords()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $startIndex = 10;
        $endIndex = 19;

        $cutText = TextMarker::cutWord($text, $startIndex, $endIndex);
        $expectedCutText = 'test text';
        
        $this->assertSame($expectedCutText, $cutText);
    }

    public function testCutWord_startOfString()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $startIndex = 3;

        $cutText = TextMarker::cutWord($text, $startIndex);
        $expectedCutText = 'This';
        
        $this->assertSame($expectedCutText, $cutText);
    }

    public function testCutWord_endOfString()
    {
        $text = 'This is a test text to test the TextMarker class';
        $startIndex = 45;

        $cutText = TextMarker::cutWord($text, $startIndex);
        $expectedCutText = 'class';
        
        $this->assertSame($expectedCutText, $cutText);
    }

    public function testCutWord_endOfString_withDot()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $startIndex = 45;

        $cutText = TextMarker::cutWord($text, $startIndex);
        $expectedCutText = 'class';
        
        $this->assertSame($expectedCutText, $cutText);
    }

    public function testCutWord_middleOfString_withSemicolon()
    {
        $text = 'This is a test text; should be cut';
        $startIndex = 17;

        $cutText = TextMarker::cutWord($text, $startIndex);
        $expectedCutText = 'text';
        
        $this->assertSame($expectedCutText, $cutText);
    }

    public function testTokenize_simpleText()
    {
        $text = 'This is a test text to test the TextMarker class.';
        $tokens = TextMarker::tokenize($text);
        $expectedTokens = ['test', 'text', 'TextMarker', 'class'];

        $this->assertSame($expectedTokens, $tokens);
    }

    public function testTokenize_doubleSpaces()
    {
        $text = 'This is a  test text to test the TextMarker class.';
        $tokens = TextMarker::tokenize($text);
        $expectedTokens = ['test', 'text', 'TextMarker', 'class'];

        $this->assertSame($expectedTokens, $tokens);
    }

    public function testTokenize_singleWord()
    {
        $text = 'Test';
        $tokens = TextMarker::tokenize($text);
        $expectedTokens = ['Test'];

        $this->assertSame($expectedTokens, $tokens);
    }

    public function testTokenize_semiColonInMiddleOfSentence()
    {
        $text = 'This is a test text; should be cut';
        $tokens = TextMarker::tokenize($text);
        $expectedTokens = ['test', 'text'];

        $this->assertSame($expectedTokens, $tokens);
    }
}