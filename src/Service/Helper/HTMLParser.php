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

        return static::extract($html, $headingHTMLTags, $remove)[0][0] ?? null;
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

        return static::extract($html, $paragraphHTMLTags, $remove)[0][0] ?? null;
    }

    /**
     * Extracts any data for the given HTML tags from the raw HTML.
     * 
     * @return array|null The extracted data from the raw HTML; null if empty content was given or no data was found.
     */
    public static function extractAllText(string &$html, bool $remove = false): ?array
    {
        $textHTMLTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'span'];

        return static::extract($html, $textHTMLTags, $remove, elementLimit: null);
    }

    /**
     * Extracts the given HTML tags from the raw HTML.
     * 
     * @param string $html The HTML to extract the tags from.
     * @param array $htmlTags The HTML tags to extract from the raw HTML.
     * @param bool $remove (default: false) Whether to remove found HTML tags from the raw HTML.
     * @param int|null $elementLimit (default: 1) The maximum number of elements to extract; default is 1; pass null to extract all elements.
     * 
     * @return array|null The extracted tags from the raw HTML; null if empty content was given or no tags were found. Structure: [['content', 'tag'], ...], e.g. [['Heading 1', 'h1'], ['Paragraph 1', 'p']]
     */
    private static function extract(string &$html, array $htmlTags, bool $remove = false, ?int $elementLimit = 1): ?array
    {
        if (\trim($html) === '') {
            return null;
        }

        $domDocument = static::parseHTML($html);
        $foundElements = [];

        // get div with ID 'main' to iterate over the children;
        // we do this to have the returned items in chronological order
        $mainDiv = $domDocument->getElementById('main');

        // iterate over the children of the document
        foreach ($mainDiv->childNodes as $htmlElement) {
            // get the text content of the element
            $textContent = \trim($htmlElement->textContent);
            $hasFoundChildElements = false;

            // first, find all the child tags that match the given HTML tags;
            // if we find any in the children we skip this parent; this is to avoid duplicate elements in the result
            foreach ($htmlElement->childNodes as $childElement) {
                $innerHTML = $childElement->ownerDocument->saveHTML($childElement);
                $foundChildElements = static::extract($innerHTML, $htmlTags, $remove, $elementLimit) ?? [];
                \array_push($foundElements, ...$foundChildElements);

                if (\count($foundChildElements) > 0) {
                    $hasFoundChildElements = true;
                }
            }

            // now check the main element;
            // if there were text elements in the children we skip this parent OR if the text content is empty OR if the tag is not in the list of allowed HTML tags
            if (!$hasFoundChildElements && '' !== $textContent && \in_array($htmlElement->nodeName, $htmlTags, true)) {
                // here we can lose data, e.g. if there is a <strong> embedded into a <p>.
                // working around this requires some more complex logic - could be done later.
                $text = \trim($htmlElement->textContent);

                if ('' !== $text) {
                    if ($remove) {
                        // now get the full HTML from the tag to remove it from the original HTML, e.g. <h1>Heading 1</h1>
                        $innerHTML = $htmlElement->ownerDocument->saveXML($htmlElement, LIBXML_NOEMPTYTAG);
                        $html = \str_replace($innerHTML, '', $html);
                    }

                    // add the found element to the list, e.g. ['Heading 1', 'h1']
                    $foundElements[] = [$text, $htmlElement->nodeName];
                }
            }

            // prevent the loop from extracting too many elements;
            // if we have a limit and the limit is reached we break the loop.
            if (null !== $elementLimit && \count($foundElements) >= $elementLimit) {
                $foundElements = \array_slice($foundElements, 0, $elementLimit); // this is to make sure to not exceed the limit; even with child tag elements
                break;
            }
        }

        return \count($foundElements) > 0 ? $foundElements : null;
    }

    /**
     * Parses the HTML into a DOMDocument by adding a meta charset and a div with ID 'main'.
     * Reading child nodes from the given $rawHTML is possible by first selecting the div and then iterating over its childNodes.
     * 
     * @param string $rawHtml The raw HTML to parse, e.g. '<p>Parsed HTML</p>'.
     * 
     * @return \DOMDocument The parsed HTML as a DOMDocument.
     */
    public static function parseHTML(string $rawHtml): \DOMDocument
    {
        $html = new HTML5();
        $domDocument = $html->loadHTML('<meta charset="utf8"><div id="main">'.$rawHtml.'</div>');

        return $domDocument;
    }
}