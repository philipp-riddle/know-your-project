<?php

namespace App\Tests\Application;

class HomeControllerTest extends ApplicationTestCase
{
    public function testHomeUnauthorized(): void
    {
        self::$client->request('GET', '/');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('http://localhost/auth/login');
    }

    public function testHomeAuthorized(): void
    {
        self::$client->loginUser($this->createUser());
        self::$client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }
}