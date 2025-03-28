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

        // make sure all security headers are set when loading the application and the frontend
        $this->assertResponseHasHeader('Content-Security-Policy');
        $this->assertResponseHasHeader('X-Content-Type-Options');
        $this->assertResponseHasHeader('X-Frame-Options');
        $this->assertResponseHasHeader('X-XSS-Protection');
        $this->assertResponseHasHeader('Permissions-Policy');
    }
}