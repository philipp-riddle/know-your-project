<?php

namespace App\Service\Helper;

use App\Exception\HTML\HTMLValidationException;

class HTMLValidator
{
    public const ALLOWED_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'li', 'ul', 'ol', 'strong', 'i', 's', 'u', 'b', 'a', 'img', 'em'];

    public static function validate(string $html): void
    {
        $domDocument = HTMLParser::parseHTML($html);
        $mainDiv = $domDocument->getElementById('main'); // this is where our $html was stored; get the div and extract its child nodes, recursively

        // iterate over the children of the document
        foreach ($mainDiv->childNodes as $htmlElement) {
            self::validateNode($htmlElement);
        }
    }

    private static function validateNode(\DOMNode $node): void
    {
        $nodeName = $node->nodeName;
        $innerHTML = $node->ownerDocument->saveHTML($node);

        if ($nodeName !== '#text' && !\in_array($nodeName, self::ALLOWED_TAGS, true)) {
            throw new HTMLValidationException(\sprintf('The tag "%s" is not allowed (Inner HTML: %s).', $node->nodeName, $innerHTML));
        }

        foreach ($node->attributes ?? [] as $attribute) {
            if (\str_starts_with($attribute->name, 'on')) { // e.g. 'onload', 'onclick', etc.
                throw new HTMLValidationException(\sprintf('The attribute "%s" is not allowed (Inner HTML: %s).', $attribute->name, $innerHTML));
            }

            if ($attribute->name === 'src') {
                // attackers could bypass the validation by adding spaces to the source
                $source = \trim(\str_replace(' ', '', $attribute->value));

                // check if the source is a javascript or data source; this is not allowed; only URLs!
                if (\str_starts_with($source, 'javascript:') || \str_starts_with($source, 'data:')) {
                    throw new HTMLValidationException(\sprintf('Adding javascript or data as a source is not allowed (Inner HTML: %s).', $innerHTML));
                }
            }

            if ($attribute->name === 'style') {
                // attackers could bypass the validation by adding spaces to the style
                $style = \trim(\str_replace(' ', '', $attribute->value));

                // check if the style contains the 'url' function; this is not allowed in inline HTML.
                if (\str_contains($style, 'url(')) {
                    throw new HTMLValidationException(\sprintf('Adding a URL in the style is not allowed (Inner HTML: %s).', $innerHTML));
                }

                // check if the style contains the 'expression' function; this is not allowed in inline HTML.
                if (\str_contains($style, 'expression(')) {
                    throw new HTMLValidationException(\sprintf('Adding an expression in the style is not allowed (Inner HTML: %s).', $innerHTML));
                }

                // check if the style contains the 'javascript:' protocol; this is not allowed in inline HTML.
                if (\str_contains($style, 'javascript:')) {
                    throw new HTMLValidationException(\sprintf('Adding javascript in the style is not allowed (Inner HTML: %s).', $innerHTML));
                }
            }
        }

        // now check the node's children and validate recursively
        foreach ($node->childNodes as $childNode) {
            self::validateNode($childNode);
        }
    }
}