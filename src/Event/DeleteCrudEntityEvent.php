<?php

namespace App\Event;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\User\User;

class DeleteCrudEntityEvent extends CrudEntityEvent
{
    public function __construct(
        CrudEntityInterface $entity,
        User $user,
        protected int $entityId,
    ) {
        parent::__construct($entity, $user);
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}