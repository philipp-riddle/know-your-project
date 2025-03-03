<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('json_variable_encode', array($this, 'jsonVariableEncode')),
        ];
    }

    /**
     * Taken from https://mathiasbynens.be/notes/json-dom-csp
     */
    public function jsonVariableEncode($object): string
    {
        return \json_encode($object, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
    }
}
