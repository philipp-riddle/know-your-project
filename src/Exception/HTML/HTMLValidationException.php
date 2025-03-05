<?php

namespace App\Exception\HTML;

use App\Exception\BadRequestException;

class HTMLValidationException extends BadRequestException
{
    public function getProductionMessage(): string
    {
        return 'The HTML provided is invalid. Please make sure that the HTML is valid and does not contain any disallowed tags.';
    }
}