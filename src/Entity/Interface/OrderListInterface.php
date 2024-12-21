<?php

namespace App\Entity\Interface;

interface OrderListInterface
{
    /**
     * @return OrderListItemInterface[]
     */
    public function getOrderedListItems(): array; 
}