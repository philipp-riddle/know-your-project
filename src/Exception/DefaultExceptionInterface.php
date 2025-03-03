<?php

namespace App\Exception;

interface DefaultExceptionInterface
{
    /**
     * Message to return in the production environment.
     * By default it returns the same message as the development environment.
     */
    public function getProductionMessage(): string;
}