<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new \Twig\TwigFilter('json_variable_encode', array($this, 'jsonVariableEncode')),
        ];
    }

    /**
     * Taken from https://mathiasbynens.be/notes/json-dom-csp.
     * Otherwise it is not possible for us to use the variable json-encoded in a script tag.
     */
    public function jsonVariableEncode($object): string
    {
        return \json_encode($object, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
    }
}
