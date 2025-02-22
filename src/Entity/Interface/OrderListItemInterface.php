<?php

namespace App\Entity\Interface;

/**
 * An entity can implement this interface if it has an order index and can thus be reordered by the user.
 */
interface OrderListItemInterface
{
    public function getId(): ?int;

    public function getOrderIndex(): ?int;

    public function setOrderIndex(int $orderIndex): static;
}