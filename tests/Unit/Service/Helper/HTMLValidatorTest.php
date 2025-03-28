<?php

namespace App\Tests\Unit\Service\Helper;

use App\Exception\HTML\HTMLValidationException;
use App\Service\Helper\HTMLValidator;
use PHPUnit\Framework\TestCase;

class HTMLValidatorTest extends TestCase
{
    public function testValidate_validHtml_simpleParagraph()
    {
        $html = '<p>Paragraph 1</p>';
        HTMLValidator::validate($html);

        $this->expectNotToPerformAssertions();
    }

    public function testValidate_validHtml_severalHtmlTags()
    {
        $html = '<h1>Heading 1</h1><p>Paragraph 1</p><h2>Heading 2</h2>';
        HTMLValidator::validate($html);

        $this->expectNotToPerformAssertions();
    }

    public function testValidate_validHtml_realApplicationCase()
    {
        $html = '<h2>Frequently Asked Questions</h2><ul><li><p><strong>What is \"Know Your Project\"?</strong><br>This initiative aims to improve knowledge sharing and project management efficiency within teams, ensuring that critical information is accessible to everyone.</p></li><li><p><strong>Why is this important?</strong><br>Effective communication and documentation reduce inefficiencies, allowing team members to work collaboratively without constantly seeking information from others.</p></li><li><p><strong>Who is involved in this initiative?</strong><br>All team members, including software engineers, product managers, support staff, and customers, are encouraged to participate in enhancing knowledge sharing.</p></li><li><p><strong>How can I contribute to \"Know Your Project\"?</strong><br>You can contribute by documenting relevant information, sharing insights from your work, and collaborating with colleagues to consolidate knowledge.</p></li><li><p><strong>Where can I find the project documentation?</strong><br>Documentation can be retrieved from our centralized knowledge management system where all relevant resources are stored.</p></li></ul><p>For any additional queries, please collaborate with your team leads or check the documentation system for more details.</p><p></p>';
        HTMLValidator::validate($html);

        $this->expectNotToPerformAssertions();
    }

    public function testValidate_invalidHTML_shouldBeTreatedAsText()
    {
        $html = '{"test": "invalid"}';
        HTMLValidator::validate($html);

        $this->expectNotToPerformAssertions();
    }

    public function testValidate_exception_containsDisallowedTags_simpleAndNested()
    {
        // generated with AI
        $disallowedTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'style', 'link', 'meta', 'base', 'title', 'head', 'html', 'body', 'frameset', 'frame', 'noframes', 'applet', 'basefont', 'bgsound', 'blink', 'isindex', 'keygen', 'listing', 'marquee', 'menu', 'nextid', 'noembed', 'plaintext', 'spacer', 'xmp'];

        foreach ($disallowedTags as $disallowedTag) {
            $html = "<$disallowedTag>Disallowed tag</$disallowedTag>";

            try {
                HTMLValidator::validate($html);
                $this->assertTrue(false, 'The test should have thrown an exception, Tag: ' . $disallowedTag);
            } catch (HTMLValidationException $e) {
                $this->assertStringContainsString($disallowedTag, $e->getMessage());
            }

            $nestedHtml = "<div>$html</div>";

            try {
                HTMLValidator::validate($nestedHtml);
                $this->assertTrue(false, 'The nested test should have thrown an exception, Tag: ' . $disallowedTag);
            } catch (HTMLValidationException $e) {
                $this->assertStringContainsString($disallowedTag, $e->getMessage());
            }
        }
    }

    public function testValidate_exception_containsOnload()
    {
        $html = '<div onload="alert(\'XSS\')">Onload attribute</div>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('onload', $e->getMessage());
        }
    }

    public function testValidate_exception_containsOnClick()
    {
        $html = '<div onclick="alert(\'XSS\')">Onclick attribute</div>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('onclick', $e->getMessage());
        }
    }

    public function testValidate_exception_a_onMouseOver()
    {
        $html = '<a href="#" onmouseover="alert(\'XSS\')">OnMouseOver attribute</a>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('onmouseover', $e->getMessage());
        }
    }

    public function testValidate_exception_containsInvalidSource_javascript()
    {
        $html = '<img src="javascript:alert(\'XSS\')" alt="Invalid source">';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('javascript:', $e->getMessage());
        }
    }

    public function testValidate_exception_containsInvalidSource_data()
    {
        $html = '<img src="data:image/png;base64,invalid" alt="Invalid source">';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('data:', $e->getMessage());
        }
    }

    public function testValidate_exception_style_containsUrl()
    {
        $html = '<div style="background-image: url(\'https://example.com\')">URL in style</div>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('url(', $e->getMessage());
        }
    }

    public function testValidate_exception_style_containsUrl_andSpaces()
    {
        $html = '<div style="     background-image: url(  \'https://example.com\'  )">Spaces in style</div>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('url(', $e->getMessage());
        }
    }

    public function testValidate_exception_style_containsExpression()
    {
        $html = '<div style="background-image: expression(alert(\'XSS\'))">Expression in style</div>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('expression(', $e->getMessage());
        }
    }

    public function testValidate_exception_style_containsJavascript()
    {
        $html = '<div style="background-image: javascript:alert(\'XSS\')">Javascript in style</div>';

        try {
            HTMLValidator::validate($html);
            $this->assertTrue(false, 'The test should have thrown an exception');
        } catch (HTMLValidationException $e) {
            $this->assertStringContainsString('javascript:', $e->getMessage());
        }
    }
}