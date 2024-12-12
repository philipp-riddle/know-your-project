<?php

namespace App\Tests\Unit\Service;

use App\Entity\Interface\OrderListItemInterface;

/**
 * Small fixture class which helps us in tests - otherwise some order list behaviours are really hard to test
 */
class OrderListItem implements OrderListItemInterface
{
    public function __construct(
        public int $id,
        public int $orderIndex,
    ) { }

    public function getId(): ?int
    {
        return $this->id;
    } 

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): static
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }
}