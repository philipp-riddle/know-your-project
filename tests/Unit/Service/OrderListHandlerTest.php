<?php

namespace App\Tests\Service;

use App\Entity\WorkflowStep;
use App\Service\OrderListHandler;
use App\Tests\Unit\Service\OrderListItem;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrderListHandlerTest extends TestCase
{
    public function testAdd_noExplicitOrderIndex()
    {
        $items = [
            (new WorkflowStep())->setOrderIndex(1)->setName('name 1'), // distraction: index starts at 1; refresh will move it back to 0
            (new WorkflowStep())->setOrderIndex(2)->setName('name 1'),
            (new WorkflowStep())->setOrderIndex(3)->setName('name 1'),
        ];
        $itemToAdd = (new WorkflowStep())->setName('name 4');
        
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
            (new WorkflowStep())->setOrderIndex(0)->setName('name 1'),
            (new WorkflowStep())->setOrderIndex(1)->setName('name 1'),
            (new WorkflowStep())->setOrderIndex(2)->setName('name 1'),
        ];
        $itemToAdd = (new WorkflowStep())
            ->setOrderIndex(1) // this way we need to shift
            ->setName('name 4');
        
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
        
        $this->expectException(InvalidArgumentException::class);
        (new OrderListHandler())->applyIdOrder($items, $idOrder);
    }

    public function testApplyIdOrder_invalidTypeInArray()
    {
        $items = [
            new OrderListItem(1, 0),
        ];
        $idOrder = [['array_not_valid']];
        
        $this->expectException(InvalidArgumentException::class);
        (new OrderListHandler())->applyIdOrder($items, $idOrder);
    }
}