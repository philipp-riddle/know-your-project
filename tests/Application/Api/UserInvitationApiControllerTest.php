<?php

namespace App\Tests\Application\Api;

use App\Entity\UserInvitation;

class UserInvitationApiControllerTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        UserInvitation::class,
    ];

    public function testCreateUserInvitation(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertSame($createResponse['email'], 'test@company.io');
        $this->assertSame($createResponse['project']['id'], self::$loggedInUser->getSelectedProject()->getId());
    }

    public function testCreateUserInvitation_error_invalidProject(): void
    {
        $this->requestApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => 999,
        ], expectStatusCode: 400);
    }

    public function testCreateUserInvitation_error_403_otherUser(): void
    {
        self::$client->loginUser($this->createUser());

        $this->requestApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ], expectStatusCode: 403);
    }

    public function testCreateUserInvitation_error_400_invitationAlreadyExists(): void
    {
        $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $this->requestApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ], expectStatusCode: 400);
    }

    public function testCreateUserInvitation_error_400_userAlreadyExists(): void
    {
        $this->requestApi('POST', '/user/invitation', [
            'email' => self::$loggedInUser->getEmail(),
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ], expectStatusCode: 400);
    }

    public function testDeleteUserInvitation(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $response = $this->requestJsonApi('DELETE', '/user/invitation/' . $createResponse['id']);
        $this->assertSame(['success' => true], $response);

        $this->requestApi('DELETE', '/user/invitation/' . $createResponse['id'], expectStatusCode: 404);
    }

    public function testDeleteUserInvitation_error_404(): void
    {
        $this->requestApi('DELETE', '/user/invitation/999', expectStatusCode: 404);
    }

    public function testDeleteUserInviutation_error_403_otherUser(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('DELETE', '/user/invitation/' . $createResponse['id'], expectStatusCode: 403);
    }

    public function testGetProjectInvitationList(): void
    {
        // first, create some invitations
        $createResponse1 = $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        $createResponse2 = $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test2@company.io',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        // distraction: create an invitation for another project and check if it is returned
        $newUser = $this->createUser();
        self::$client->loginUser($newUser);
        $this->requestJsonApi('POST', '/user/invitation', [
            'email' => 'test3@company.io',
            'project' => $newUser->getSelectedProject()->getId(),
        ]);

        // now, get the list of invitations and login back the original user
        self::$client->loginUser(self::$loggedInUser);
        $response = $this->requestJsonApi('GET', '/user/invitation/project/list/' . self::$loggedInUser->getSelectedProject()->getId());

        $this->assertCount(2, $response);
        $this->assertSame($createResponse1['id'], $response[0]['id']);
        $this->assertSame($createResponse2['id'], $response[1]['id']);
    }

    public function testGetProjectInvitationList_error_403_otherUser(): void
    {
        self::$client->loginUser($this->createUser());
        $this->requestApi('GET', '/user/invitation/project/list/' . self::$loggedInUser->getSelectedProject()->getId(), expectStatusCode: 403);
    }
}