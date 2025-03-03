<?php

namespace App\Tests\Application\Authentication;

use App\Tests\Application\ApplicationTestCase;

class LoginControllerTest extends ApplicationTestCase
{
    public function testLoginAuthorized(): void
    {
        self::$client->loginUser($this->createUser());
        self::$client->request('GET', '/auth/login');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testLoginUnauthorized(): void
    {
        self::$client->request('GET', '/auth/login');

        $this->assertResponseIsSuccessful();

        // make sure all security headers are set when loading the application and the frontend
        $this->assertResponseHasHeader('Content-Security-Policy');
        $this->assertResponseHasHeader('X-Content-Type-Options');
        $this->assertResponseHasHeader('X-Frame-Options');
        $this->assertResponseHasHeader('X-XSS-Protection');
        $this->assertResponseHasHeader('Permissions-Policy');
    }
}