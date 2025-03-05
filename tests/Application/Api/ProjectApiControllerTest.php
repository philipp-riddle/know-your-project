<?php

namespace App\Tests\Application\Api;

use App\Entity\Project\ProjectUser;
use App\Entity\User\User;

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

    public function testCreateProject(): void
    {
        $currentlySelectedProject = self::$loggedInUser->getSelectedProject();
        $response = $this->requestJsonApi('POST', '/project', [
            'name' => 'Test Project',
        ]);

        $this->assertNotNull($response['id']);
        $this->assertSame($response['name'], 'Test Project');
        $this->assertSame(self::$loggedInUser->getId(), $response['owner']['id']);

        $fetchedUser = self::$em->getRepository(User::class)->find(self::$loggedInUser->getId());
        $this->assertSame($fetchedUser->getSelectedProject()->getId(), $currentlySelectedProject->getId());
    }

    public function testCreateProject_selectAfterCreating(): void
    {
        $response = $this->requestJsonApi('POST', '/project', [
            'name' => 'Test Project',
            'selectAfterCreating' => true,
        ]);

        $this->assertNotNull($response['id']);
        $this->assertSame($response['name'], 'Test Project');
        $this->assertSame(self::$loggedInUser->getId(), $response['owner']['id']);

        $fetchedUser = self::$em->getRepository(User::class)->find(self::$loggedInUser->getId());
        $this->assertSame($fetchedUser->getSelectedProject()->getId(), $response['id']);
    }

    public function testSelectProject(): void
    {
        $project2 = $this->createProject(self::$loggedInUser);
        $response = $this->requestJsonApi('PUT', '/project/select/' . $project2->getId());

        $this->assertSame($project2->getId(), $response['id']);
    }

    public function testSelectProject_alreadySelected(): void
    {
        $response = $this->requestJsonApi('PUT', '/project/select/' . self::$loggedInUser->getSelectedProject()->getId());

        $this->assertSame(self::$loggedInUser->getSelectedProject()->getId(), $response['id']);
    }

    public function testSelectProject_exception_403_noAccess(): void
    {
        $project2 = $this->createProject($this->createUser());
        $this->requestApi('PUT', '/project/select/' . $project2->getId(), expectStatusCode: 403);
    }

    public function testDeleteProject_notSelected(): void
    {
        $project2 = $this->createProject(self::$loggedInUser);
        $response = $this->requestJsonApi('DELETE', '/project/' . $project2->getId());

        $this->assertSame(['success' => true], $response);

        $fetchedUser = self::$em->getRepository(User::class)->find(self::$loggedInUser->getId());
        $this->assertSame(self::$loggedInUser->getSelectedProject()->getId(), $fetchedUser->getSelectedProject()?->getId());
    }

    public function testDeleteProject_selected_onlyInOneProject(): void
    {
        $response = $this->requestJsonApi('DELETE', '/project/' . self::$loggedInUser->getSelectedProject()->getId());

        $this->assertSame(['success' => true], $response);

        $fetchedUser = self::$em->getRepository(User::class)->find(self::$loggedInUser->getId());
        $this->assertNull($fetchedUser->getSelectedProject());
    }

    public function testDeleteProject_selected_inTwoProjects(): void
    {
        $project2 = $this->createProject(self::$loggedInUser);
        $response = $this->requestJsonApi('DELETE', '/project/' . self::$loggedInUser->getSelectedProject()->getId());

        $this->assertSame(['success' => true], $response);

        $fetchedUser = self::$em->getRepository(User::class)->find(self::$loggedInUser->getId());
        $this->assertSame($project2->getId(), $fetchedUser->getSelectedProject()?->getId());
    }

    public function testDeleteProject_exception_403_noAccess(): void
    {
        $project2 = $this->createProject($this->createUser());
        $this->requestApi('DELETE', '/project/' . $project2->getId(), expectStatusCode: 403);
    }

    public function testDeleteProject_exception_403_notOwner(): void
    {
        $user2 = $this->createUser();
        $projectUser = (new ProjectUser())
            ->setUser($user2)
            ->setProject(self::$loggedInUser->getSelectedProject())
            ->initialize();
        self::$em->persist($projectUser);
        self::$em->flush();

        self::$client->loginUser($user2);
        $this->requestApi('DELETE', '/project/' . $projectUser->getProject()->getId(), expectStatusCode: 403);
    }
}