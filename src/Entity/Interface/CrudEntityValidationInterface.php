<?php

namespace App\Entity\Interface;

/**
 * This interface can be implemented by entities that need to validate their data before being persisted.
 * Our base CrudApiController automatically checks if a processed entity implements this interface and calls the validate() method.
 */
interface CrudEntityValidationInterface
{
    /**
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function validate(): void;
}