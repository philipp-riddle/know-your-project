<?php

namespace App\Tests\Unit\Service;

use App\Entity\Task;
use App\Service\OrderListHandler;
use App\Tests\Unit\Service\OrderListItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderListHandlerTest extends TestCase
{
    public function testAdd_noExplicitOrderIndex()
    {
        $items = [
            (new Task())->setOrderIndex(1), // distraction: index starts at 1; refresh will move it back to 0
            (new Task())->setOrderIndex(2),
            (new Task())->setOrderIndex(3),
        ];
        $itemToAdd = (new Task());
        
        (new OrderListHandler())->add($itemToAdd, $items);

        $this->assertEquals(3, $itemToAdd->getOrderIndex());
        $this->assertEquals(0, $items[0]->getOrderIndex());
        $this->assertEquals(1, $items[1]->getOrderIndex());
        $this->assertEquals(2, $items[2]->getOrderIndex());
        $this->assertEquals(3, $items[3]->getOrderIndex());
    }

    public function testAdd_explicitOrderIndex()
    {
        $items = [
            (new Task())->setOrderIndex(0),
            (new Task())->setOrderIndex(1),
            (new Task())->setOrderIndex(2),
        ];
        $itemToAdd = (new Task())
            ->setOrderIndex(1); // this way we need to shift the other items

        (new OrderListHandler())->add($itemToAdd, $items);

        $this->assertEquals(1, $itemToAdd->getOrderIndex());
        $this->assertEquals(0, $items[0]->getOrderIndex());
        $this->assertEquals(2, $items[1]->getOrderIndex());
        $this->assertEquals(3, $items[2]->getOrderIndex());
        $this->assertEquals(1, $items[3]->getOrderIndex());
    }

    public function testApplyIdOrder_default()
    {
        $items = [
            new OrderListItem(1, 0),
            new OrderListItem(2, 1),
            new OrderListItem(3, 2),
        ];
        $idOrder = [3, 2, 1]; // this order reverses the array
        
        (new OrderListHandler())->applyIdOrder($items, $idOrder);

        $this->assertEquals(2, $items[0]->getOrderIndex());
        $this->assertEquals(1, $items[1]->getOrderIndex());
        $this->assertEquals(0, $items[2]->getOrderIndex());
    }

    public function testApplyIdOrder_mismatchingCounts()
    {
        $items = [
            new OrderListItem(1, 0),
        ];
        $idOrder = [3, 2];
        
        $this->expectException(BadRequestHttpException::class);
        (new OrderListHandler())->applyIdOrder($items, $idOrder);
    }

    public function testApplyIdOrder_repeatingIdsInOrder()
    {
        $items = [
            new OrderListItem(1, 0),
            new OrderListItem(2, 1),
        ];
        $idOrder = [2, 2];
        
        $this->expectException(BadRequestHttpException::class);
        (new OrderListHandler())->applyIdOrder($items, $idOrder);
    }

    public function testApplyIdOrder_invalidTypeInArray()
    {
        $items = [
            new OrderListItem(1, 0),
        ];
        $idOrder = [['array_not_valid']];
        
        $this->expectException(BadRequestHttpException::class);
        (new OrderListHandler())->applyIdOrder($items, $idOrder);
    }
}