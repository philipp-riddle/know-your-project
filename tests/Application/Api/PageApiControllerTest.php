<?php

namespace App\Tests\Application\Api;

use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageTab;

class PageApiControllerTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        Page::class,
        PageTab::class,
        PageSection::class,
    ];

    public function testCreateAndGetPage_default(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertSame($createResponse['name'], 'Test Page');
        $this->assertSame($createResponse['project']['id'], self::$loggedInUser->getSelectedProject()->getId());
        $this->assertNull($createResponse['user']);

        $getResponse = $this->requestJsonApi('GET', '/page/' . $createResponse['id']);
        $this->assertSame($createResponse['id'], $getResponse['id']);

        $pageTabs = $getResponse['pageTabs'];
        $this->assertIsArray($pageTabs);
        $this->assertCount(1, $pageTabs);

        $pageSections = $pageTabs[0]['pageSections'];
        $this->assertIsArray($pageSections);
        $this->assertCount(1, $pageSections);
        $this->assertArrayHasKey('pageSectionText', $pageSections[0]);
        $this->assertSame($createResponse['pageTabs'][0]['pageSections'][0]['pageSectionText']['id'], $pageSections[0]['pageSectionText']['id']);
    }

    public function testCreateAndGetPage_userNote(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
            'user' => self::$loggedInUser->getId(),
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertSame($createResponse['name'], 'Test Page');
        $this->assertSame($createResponse['project']['id'], self::$loggedInUser->getSelectedProject()->getId());
        $this->assertSame($createResponse['user']['id'], self::$loggedInUser->getId());

        $getResponse = $this->requestJsonApi('GET', '/page/' . $createResponse['id']);
        $this->assertSame($createResponse['id'], $getResponse['id']);   
    }

    public function testCreatePage_error_invalidUser(): void
    {
        $this->requestApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
            'user' => 999,
        ], expectStatusCode: 400);
    }

    public function testCreatePage_error_invalidProject(): void
    {
        $this->requestApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => 999,
        ], expectStatusCode: 400);
    }

    public function testGetPage_error_403_otherUser(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('GET', '/page/' . $createResponse['id'], expectStatusCode: 403);
    }

    public function testDeletePage(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $response = $this->requestJsonApi('DELETE', '/page/' . $createResponse['id']);
        $this->assertSame(['success' => true], $response);

        // check if page is really deleted
        $this->requestApi('GET', '/page/' . $createResponse['id'], expectStatusCode: 404);
    }

    public function testDeletePage_error_otherUser()
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('DELETE', '/page/' . $createResponse['id'], expectStatusCode: 403);
    }

    public function testUpdatePage(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $updateResponse = $this->requestJsonApi('PUT', '/page/' . $createResponse['id'], [
            'name' => 'Updated Page',
        ]);

        $this->assertSame($updateResponse['name'], 'Updated Page');
    }

    public function testUpdatePage_error_403_otherUser(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('PUT', '/page/' . $createResponse['id'], [
            'name' => 'Updated Page',
        ], expectStatusCode: 403);
    }

    public function testUpdatePage_error_403_tryToUpdateProject(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);

        $this->requestApi('PUT', '/page/' . $createResponse['id'], [
            'name' => 'Updated Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ], expectStatusCode: 400);
    }

    public function testProjectList_withNoTags(): void
    {
        // create three pages in the project and one user note to test the filtering
        $createResponse1 = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page 1',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        $createResponse2 = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page 2',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        $createResponse3 = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page 3',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        $createResponseUser = $this->requestJsonApi('POST', '/page', [
            'name' => 'User Note',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
            'user' => self::$loggedInUser->getId(),
        ]);

        $response = $this->requestJsonApi('GET', \sprintf('/page/project-list/%d?tags=[]', self::$loggedInUser->getSelectedProject()->getId()));
        $this->assertCount(4, $response);
        
        // first, the user note
        $this->assertSame($createResponseUser['id'], $response[0]['id']);

        // then all the other pages
        $this->assertSame($createResponse1['id'], $response[1]['id']);
        $this->assertSame($createResponse2['id'], $response[2]['id']);
        $this->assertSame($createResponse3['id'], $response[3]['id']);

        // @todo this throws an exception because the project:owner entity is new and not persisted somehow
        // now test the filtering by requesting with another user - the private note should not show up
        self::$client->loginUser($this->createUser(selectedProject: self::$loggedInUser->getSelectedProject()));
        $response = $this->requestJsonApi('GET', \sprintf('/page/project-list/%d?tags=[]', self::$loggedInUser->getSelectedProject()->getId()));
        $this->assertCount(3, $response);
    }

    public function testProject_list_error_403_noAccessToProject(): void
    {
        $user = $this->createUser();

        $this->requestApi('GET', '/page/project-list/'.$user->getSelectedProject()->getId(), expectStatusCode: 403);
    }
}