<?php

namespace App\Event;

use App\Entity\Interface\CrudEntityInterface;

class UpdateCrudEntityEvent extends CrudEntityEvent
{
    public function __construct(
        CrudEntityInterface $entity,
        protected CrudEntityInterface $originalEntity,
    ) {
        parent::__construct($entity);
    }

    public function getOriginalEntity(): CrudEntityInterface
    {
        return $this->originalEntity;
    }
}