<?php

namespace App\Service\File\Interface;

use App\Entity\File;

/**
 * This interface is used to mark entities that are related to files.
 */
interface EntityFileInterface
{
    public function getFile(): ?File;
}