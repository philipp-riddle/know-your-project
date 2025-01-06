<?php

namespace App\Tests\Application\Api;

class UserApiControllerTest extends ApiControllerTestCase
{
    public function testGetUser(): void
    {
        self::$client->request('GET', '/api/user');
        $this->assertResponseIsSuccessful();

        $response = self::$client->getResponse();
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent(), true);
        $this->assertSame(self::$loggedInUser->getId(), $json['id']);
        $this->assertSame(self::$loggedInUser->getEmail(), $json['email']);
    }
}