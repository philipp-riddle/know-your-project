<?php

namespace App\Tests\Application\Api;

use App\Entity\Page\Page;
use App\Entity\Task;

class TaskApiControllerTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        Task::class,
        Page::class,
    ];

    public function testCreateAndGetTask(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task',
            'stepType' => 'Discover',
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertSame($createResponse['page']['name'], 'Test Task');
        $this->assertSame($createResponse['stepType'], 'Discover');
        $this->assertSame($createResponse['project']['id'], self::$loggedInUser->getSelectedProject()->getId());

        $getResponse = $this->requestJsonApi('GET', '/task/' . $createResponse['id']);
        $this->assertSame($createResponse['id'], $getResponse['id']);
    }

    public function testCreateTask_error_invalidStepType(): void
    {
        $this->requestApi('POST', '/task/InvalidStepType', [
            'name' => 'Test Task',
            'stepType' => 'InvalidStepType',
        ], expectStatusCode: 400);
    }

    public function testGetTask_error_404(): void
    {
        $this->requestApi('GET', '/task/999', expectStatusCode: 404);
    }

    public function testGetTask_error_fromOtherUser(): void
    {
        // create task with currently logged-in user
        $createResponse = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task',
            'stepType' => 'Discover',
        ]);

        // try to get task with other user
        self::$client->loginUser($this->createUser());
        $this->requestApi('GET', '/task/' . $createResponse['id'], expectStatusCode: 403);
    }

    public function testUpdateTask(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task',
            'stepType' => 'Discover',
        ]);

        $updateResponse = $this->requestJsonApi('PUT', '/task/' . $createResponse['id'], [
            'stepType' => 'Define',
        ]);

        $this->assertSame($createResponse['id'], $updateResponse['id']);
        $this->assertSame($updateResponse['page']['name'], 'Test Task');
        $this->assertSame($updateResponse['stepType'], 'Define');
    }

    public function testUpdateTask_error_403_otherUser(): void
    {
        // create task with currently logged-in user
        $createResponse = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task',
            'stepType' => 'Discover',
        ]);

        // try to update task with other user
        self::$client->loginUser($this->createUser());
        $this->requestApi('PUT', '/task/' . $createResponse['id'], [
            'name' => 'Updated Task',
            'stepType' => 'Define',
        ], expectStatusCode: 403);
    }

    public function testChangeTaskOrder(): void
    {
        $createResponse1 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 1',
            'stepType' => 'Discover',
        ]);
        $createResponse2 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 2',
            'stepType' => 'Discover',
        ]);
        $createResponse3 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 3',
            'stepType' => 'Discover',
        ]);
        $newOrder = [$createResponse3['id'], $createResponse1['id'], $createResponse2['id']];

        $orderResponse = $this->requestJsonApi('POST', '/task/Discover/order', ['idOrder' => $newOrder]);

        $this->assertSame($orderResponse[0]['orderIndex'], 0);
        $this->assertSame($orderResponse[1]['orderIndex'], 1);
        $this->assertSame($orderResponse[2]['orderIndex'], 2);
    }

    public function testChangeTaskOrder_error_tooManyItems(): void
    {
        $createResponse1 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 1',
            'stepType' => 'Discover',
        ]);
        $newOrder = [$createResponse1['id'], $createResponse1['id']];

        $this->requestApi('POST', '/task/Discover/order', ['idOrder' => $newOrder], expectStatusCode: 400);
    }

    public function testChangeTaskOrder_error_invalidUser(): void
    {
        $createResponse1 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 1',
            'stepType' => 'Discover',
        ]);
        $newOrder = [$createResponse1['id']];

        self::$client->loginUser($this->createUser());
         // if we do not create a task with the other user the order cannot be set as the count of items to order does not match
        $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 1',
            'stepType' => 'Discover',
        ]);

        // now try to set the order
        $this->requestApi('POST', '/task/Discover/order', ['idOrder' => $newOrder], expectStatusCode: 404);
    }

    public function testMoveTask(): void
    {
        // first, create three tasks to move
        $createResponse1 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 1',
            'stepType' => 'Discover',
        ]);
        $createResponse2 = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task 2',
            'stepType' => 'Discover',
        ]);
        $createResponse3 = $this->requestJsonApi('POST', '/task/Define', [
            'name' => 'Test Task 3',
            'stepType' => 'Define',
        ]);

        // move the first task to the 'Define' step
        $moveResponse = $this->requestJsonApi('POST', '/task/' . $createResponse1['id'] . '/move', [
            'stepType' => 'Define',
            'orderIndex' => 1, // at the end of the list
        ]);

        $this->assertSame($moveResponse['stepType'], 'Define');
        $this->assertSame($moveResponse['orderIndex'], 1);

        // move the second task to the first position in the 'Define' step
        $moveResponse = $this->requestJsonApi('POST', '/task/' . $createResponse2['id'] . '/move', [
            'stepType' => 'Define',
            'orderIndex' => 0,
        ]);

        $this->assertSame($moveResponse['stepType'], 'Define');
        $this->assertSame($moveResponse['orderIndex'], 0);

        // to be safe check the other indices of the other tasks

        $getResponse1 = $this->requestJsonApi('GET', '/task/' . $createResponse3['id']);
        $this->assertSame($getResponse1['orderIndex'], 1);

        $getResponse2 = $this->requestJsonApi('GET', '/task/' . $createResponse1['id']);
        $this->assertSame($getResponse2['orderIndex'], 2);
    }

    public function testMoveTask_error_403_otherUser(): void
    {
        // create task with currently logged-in user
        $createResponse = $this->requestJsonApi('POST', '/task/Discover', [
            'name' => 'Test Task',
            'stepType' => 'Discover',
        ]);

        // try to move task with other user
        self::$client->loginUser($this->createUser());
        $this->requestApi('POST', '/task/' . $createResponse['id'] . '/move', [
            'stepType' => 'Define',
            'orderIndex' => 1,
        ], expectStatusCode: 403);
    }
}