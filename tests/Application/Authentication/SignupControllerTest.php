<?php

namespace App\Tests\Application\Authentication;

use App\Tests\Application\ApplicationTestCase;

class SignupControllerTest extends ApplicationTestCase
{
    public function testSignupAuthorized(): void
    {
        self::$client->loginUser($this->createUser());
        self::$client->request('GET', '/auth/signup');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testSigupUnauthorized(): void
    {
        self::$client->request('GET', '/auth/signup');

        $this->assertResponseIsSuccessful();

        // make sure all security headers are set when loading the application and the frontend
        $this->assertResponseHasHeader('Content-Security-Policy');
        $this->assertResponseHasHeader('X-Content-Type-Options');
        $this->assertResponseHasHeader('X-Frame-Options');
        $this->assertResponseHasHeader('X-XSS-Protection');
        $this->assertResponseHasHeader('Permissions-Policy');
    }
}