<?php

namespace App\Event;

use App\Entity\User\User;
use Symfony\Contracts\EventDispatcher\Event;

class OrderCrudEntitiesEvent extends Event
{
    public function __construct(
        private string $entityClass, // e.g. Task::class
        private ?string $orderListName, // e.g. 'Discover'
        private array $itemsToOrder,
        private User $user,
    ) { }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getOrderListName(): ?string
    {
        return $this->orderListName;
    }

    public function getItemsToOrder(): array
    {
        return $this->itemsToOrder;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}