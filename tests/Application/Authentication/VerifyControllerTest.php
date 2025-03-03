<?php

namespace App\Tests\Application\Authentication;

use App\Tests\Application\ApplicationTestCase;

class VerifyControllerTest extends ApplicationTestCase
{
    public function testVerifyAuthorized(): void
    {
        self::$client->loginUser($this->createUser());
        self::$client->request('GET', '/auth/verify/123');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testVerifyUnauthorized(): void
    {
        self::$client->request('GET', '/auth/verify/123');

        $this->assertResponseIsSuccessful();

        // make sure all security headers are set when loading the application and the frontend
        $this->assertResponseHasHeader('Content-Security-Policy');
        $this->assertResponseHasHeader('X-Content-Type-Options');
        $this->assertResponseHasHeader('X-Frame-Options');
        $this->assertResponseHasHeader('X-XSS-Protection');
        $this->assertResponseHasHeader('Permissions-Policy');
    }
}