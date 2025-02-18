<?php

namespace App\Event;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\User\User;
use Symfony\Contracts\EventDispatcher\Event;

abstract class CrudEntityEvent extends Event
{
    public function __construct(
        protected CrudEntityInterface $entity,
        protected User $user,
    ) { }

    public function getEntity(): CrudEntityInterface
    {
        return $this->entity;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}