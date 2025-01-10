<?php

namespace App\Event;

use App\Entity\Interface\CrudEntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class CrudEntityEvent extends Event
{
    public function __construct(
        protected CrudEntityInterface $entity
    ) { }

    public function getEntity(): CrudEntityInterface
    {
        return $this->entity;
    }
}