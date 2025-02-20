<?php

namespace App\Service;

use App\Entity\Interface\OrderListItemInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderListHandler
{
    public function add(OrderListItemInterface $orderListItem, array &$orderListItems): void
    {
        if (null === $orderListItem->getOrderIndex()) { // if the new item has no specified index, add it to the end of the list
            $orderListItems[] = $orderListItem;
            $this->refreshIndices($orderListItems);

            return;
        }

        $this->shiftIndices($orderListItems, $orderListItem->getOrderIndex());
        $orderListItems[] = $orderListItem; // after shifting the value in the right place we can add it to the array

        // now sort the array by the order index
        \usort($orderListItems, fn(OrderListItemInterface $a, OrderListItemInterface $b) => $a->getOrderIndex() <=> $b->getOrderIndex());
    }

    /**
     * @param OrderListItemInterface[] $orderListItems
     */
    public function applyIdOrder(array &$orderListItems, array $idOrder): void
    {
        if (!\array_is_list($idOrder)) {
            throw new BadRequestHttpException('ID order must be a list of IDs');
        }

        $uniqueIdOrder = \array_unique($idOrder);

        if (\count($uniqueIdOrder) !== \count($idOrder)) {
            throw new BadRequestHttpException('ID order must be a list of unique IDs');
        }

        if (\count($idOrder) !== \count($orderListItems)) {
            throw new BadRequestHttpException('ID order must be the same size as the ordered list itself');
        }

        // create a map of the items to find the items with specific IDs quicker
        // we could also use array_search in every iteration but that's expensive in comparison
        $hashmap = [];

        foreach ($orderListItems as &$orderListItem) {
            $hashmap[$orderListItem->getId()] = $orderListItem;
        }
        
        $i = 0;

        foreach ($idOrder as $orderListItemId) {
            if (!\is_int($orderListItemId)) {
                throw new BadRequestHttpException(
                    \sprintf(
                        'Supplied IDs must be format of integer, %s found (value: "%s")',
                        \gettype($orderListItemId),
                        \json_encode($orderListItemId)
                    )
                );
            }

            if (!\array_key_exists($orderListItemId, $hashmap)) {
                throw new NotFoundHttpException(\sprintf('Item with ID %d not found in the list', $orderListItemId));
            }

            $hashmap[$orderListItemId]->setOrderIndex($i);
            $i++;
        }

        // now sort te array by the order index
        \usort($orderListItems, fn(OrderListItemInterface $a, OrderListItemInterface $b) => $a->getOrderIndex() <=> $b->getOrderIndex());
    }

    /**
     * @param OrderListItemInterface[] $orderListItems
     */
    protected function shiftIndices(array &$orderListItems, int $shiftIndex, int $shiftSize = 1): void
    {
        // first, make sure that the indices are correct before we start
        $this->refreshIndices($orderListItems);

        foreach ($orderListItems as $orderListItem) {
            if ($orderListItem->getOrderIndex() >= $shiftIndex) {
                $orderListItem->setOrderIndex($orderListItem->getOrderIndex() + $shiftSize);
            }
        }
    }

    /**
     * @param OrderListItemInterface[] $orderListItems
     */
    protected function refreshIndices(array $orderListItems): void
    {
        $indices = [];
        $newOrderListItems = [];

        foreach ($orderListItems as $orderListItem) {
            if (null === $orderListItem->getOrderIndex()) {
                $newOrderListItems[] = $orderListItem;
            } else {
                $indices[$orderListItem->getOrderIndex()][] = $orderListItem;
            }
        }

        \ksort($indices);

        $i = 0;

        // now, iterate over the stacks of indices.
        // making it possible for two items to have the same index makes this really flexible and prevents errors by design.
        foreach ($indices as $orderListItems) {
            foreach ($orderListItems as $orderListItem) {
                $orderListItem->setOrderIndex($i);
                ++$i;
            }
        }

        foreach ($newOrderListItems as $newOrderListItem) {
            $newOrderListItem->setOrderIndex($i);
            ++$i;
        }
    }
}