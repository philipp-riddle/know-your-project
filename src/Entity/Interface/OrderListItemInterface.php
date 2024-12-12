<?php

namespace App\Entity\Interface;

interface OrderListItemInterface
{
    public function getId(): ?int;

    public function getOrderIndex(): ?int;

    public function setOrderIndex(int $orderIndex): static;
}