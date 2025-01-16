<?php

namespace App\Service\File\Interface;

use App\Entity\File;

/**
 * This interface is used to mark entities that are related to no, one or multiple files.
 */
interface EntityMultipleFileInterface
{
    /**
     * @return File[]
     */
    public function getFiles(): array;
}