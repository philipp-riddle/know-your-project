<?php

namespace App\Event;

use App\Entity\Interface\CrudEntityInterface;

class DeleteCrudEntityEvent extends CrudEntityEvent
{
    public function __construct(
        CrudEntityInterface $entity,
        protected int $entityId,
    ) {
        parent::__construct($entity);
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}