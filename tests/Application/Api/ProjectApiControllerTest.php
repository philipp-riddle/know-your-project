<?php

namespace App\Tests\Application\Api;

class ProjectApiControllerTest extends ApiControllerTestCase
{
    public function testGetProject(): void
    {
        $response = $this->requestJsonApi('GET', '/project/' . self::$loggedInUser->getSelectedProject()->getId());

        $this->assertSame($response['id'], self::$loggedInUser->getSelectedProject()->getId());
    }

    public function testGetProject_error_403_otherUser(): void
    {
        self::$client->loginUser($this->createUser());
        $this->requestApi('GET', '/project/' . self::$loggedInUser->getSelectedProject()->getId(), expectStatusCode: 403);
    }
}