<?php

namespace App\Service\Helper;

use Masterminds\HTML5;

/**
 * This class is responsible for parsing HTML and extracting specific tags / content / information.
 * Possible thanks to this library: https://github.com/Masterminds/html5-php
 */
final class HTMLParser
{
    /**
     * Extracts the first <h1>/<h2> tag from the raw HTML, if any.
     * 
     * @param string $html The raw HTML to extract the heading from.
     * @param bool $remove Whether to remove the heading from the raw HTML if found.
     * 
     * @return string|null The extracted heading from the raw HTML; null if empty content was given or no heading was found.
     */
    public static function extractHeading(string &$html, bool $remove = false): ?string
    {
        $headingHTMLTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        return static::extract($html, $headingHTMLTags, $remove);
    }

    /**
     * Extracts the first <p> tag from the raw HTML.
     * 
     * @param string $html The HTML to extract the paragraph from.
     * @param bool $remove Whether to remove the paragraph from the raw HTML if found.
     * 
     * @return string|null The extracted paragraph from the raw HTML; null if empty content was given or no paragraph was found.
     */
    public static function extractParagraph(string &$html, bool $remove = false): ?string
    {
        $paragraphHTMLTags = ['p'];

        return static::extract($html, $paragraphHTMLTags, $remove);
    }

    /**
     * Extracts the given HTML tags from the raw HTML.
     * 
     * @param string $html The HTML to extract the tags from.
     * @param array $htmlTags The HTML tags to extract from the raw HTML.
     * @param bool $remove Whether to remove the HTML tag from the raw HTML.
     * 
     * @return string|null The extracted content from the raw HTML; null if empty content was given or no content was found.
     */
    public static function extract(string &$html, array $htmlTags, bool $remove = false): ?string
    {
        if (\trim($html) === '') {
            return null;
        }

        $domDocument = static::parseHTML($html);

        foreach ($htmlTags as $htmlTag) {
            $htmlElements = $domDocument->getElementsByTagName($htmlTag);

            if ($htmlElements->length > 0) {
                for ($i = 0; $i < $htmlElements->length; $i++) {
                    $htmlElement = $htmlElements->item($i);
                    $heading = \trim(\strip_tags($htmlElement->textContent));

                    if ('' !== $heading) {
                        if ($remove) {
                            // now get the full HTML from the tag to remove it from the original HTML, e.g. <h1>Heading 1</h1>
                            $innerHTML = $htmlElement->ownerDocument->saveXML($htmlElement, LIBXML_NOEMPTYTAG);
                            $html = \str_replace($innerHTML, '', $html);
                        }

                        return $heading;
                    }
                }
            }
        }

        return null;
    }

    private static function parseHTML(string $rawHtml): \DOMDocument
    {
        $html = new HTML5();
        $domDocument = $html->loadHTML('<meta charset="utf8">'.$rawHtml);

        return $domDocument;
    }
}