<?php

namespace App\Tests\Application\Api;

use App\Entity\Project\ProjectUser;

class ProjectUserApiControllerTest extends ApiControllerTestCase
{
    public function testDeleteProjectUser(): void
    {
        $newUser = $this->createUser();
        $newProjectUser = (new ProjectUser())
            ->setUser($newUser)
            ->setProject(self::$loggedInUser->getSelectedProject())
            ->setCreatedAt(new \DateTime());
        self::$em->persist($newProjectUser);
        self::$em->flush();
        $newProjectUserId = $newProjectUser->getId(); // save the ID as the ID will not be available after the entity is deleted

        $response = $this->requestJsonApi('DELETE', '/project/user/' . $newProjectUser->getId());
        $this->assertSame(['success' => true], $response);

        // check if the project user was deleted via API
        $this->requestApi('GET', '/project/user/' . $newProjectUserId, expectStatusCode: 404);
    }

    public function testDeleteProjectUser_error_403_cannotDeleteUserItself()
    {
        $this->requestApi('DELETE', '/project/user/' . self::$loggedInUser->getSelectedProject()->getProjectUsers()[0]->getId(), expectStatusCode: 400);
    }

    public function testDeleteProjectUser_error_403_notInProject()
    {
        $newUser = $this->createUser();

        $this->requestApi('DELETE', '/project/user/' . $newUser->getSelectedProject()->getProjectUsers()[0]->getId(), expectStatusCode: 403);
    }

    public function testDeleteProjectUser_error_404()
    {
        $this->requestApi('DELETE', '/project/user/999999', expectStatusCode: 404);
    }

    public function testGetProjectUser(): void
    {
        $getResponse = $this->requestJsonApi('GET', '/project/user/' . self::$loggedInUser->getSelectedProject()->getProjectUsers()[0]->getId());
        $this->assertArrayHasKey('id', $getResponse);
    }
}