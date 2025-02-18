<?php

namespace App\Event;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\User\User;

class UpdateCrudEntityEvent extends CrudEntityEvent
{
    public function __construct(
        CrudEntityInterface $entity,
        User $user,
        protected CrudEntityInterface $originalEntity,
    ) {
        parent::__construct($entity, $user);
    }

    public function getOriginalEntity(): CrudEntityInterface
    {
        return $this->originalEntity;
    }
}