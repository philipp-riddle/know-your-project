<?php

namespace App\Tests\Application\Api;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\PageSectionChecklist;
use App\Entity\PageSectionChecklistItem;
use App\Entity\PageTab;
use App\Entity\Project;

class PageSectionChecklistItemApiControllerTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        Page::class,
        PageSection::class,
        PageSectionChecklist::class,
        PageSectionChecklistItem::class,
    ];

    public function testCreateAndGetPageSectionChecklistItem(): void
    {
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);
        
        $this->assertNotNull($createResponse['id']);

        $getResponse = $this->requestJsonApi('GET', '/page/section/checklist/item/' . $createResponse['id']);
        $this->assertSame($createResponse['id'], $getResponse['id']);
    }

    public function testCreateAndGetPageSectionChecklistItem_error_403_unavailableChecklist(): void
    {
        // create a checklist and then login another user
        $checklistId = $this->getPageSectionChecklist()->getId();
        self::$client->loginUser($this->createUser());

        $this->requestApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $checklistId,
            'name' => 'Test Item',
            'complete' => false,
        ], expectStatusCode: 403);
    }

    public function testGetChecklistItem_error_404(): void
    {
        $this->requestApi('GET', '/page/section/checklist/item/999', expectStatusCode: 404);
    }

    public function testGetChecklistItem_error_403_otherUser(): void
    {
        // create checklist item with currently logged-in user
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);

        // try to get checklist item with other user
        self::$client->loginUser($this->createUser());
        $this->requestApi('GET', '/page/section/checklist/item/' . $createResponse['id'], expectStatusCode: 403);
    }

    public function testDeleteChecklistItem_default()
    {
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);

        $response = $this->requestJsonApi('DELETE', '/page/section/checklist/item/' . $createResponse['id']);
        $this->assertSame(['success' => true], $response);

        $this->requestApi('GET', '/page/section/checklist/item/' . $createResponse['id'], expectStatusCode: 404);
    }

    public function testDeleteChecklistItem_error_403_otherUser()
    {
        // create checklist item with currently logged-in user
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);

        // try to delete checklist item with other user
        self::$client->loginUser($this->createUser());
        $this->requestApi('DELETE', '/page/section/checklist/item/' . $createResponse['id'], expectStatusCode: 403);
    }

    public function testUpdateChecklistItem()
    {
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);

        $updateResponse = $this->requestJsonApi('PUT', '/page/section/checklist/item/' . $createResponse['id'], [
            'name' => 'Updated Item',
            'complete' => true,
        ]);

        $this->assertSame('Updated Item', $updateResponse['name']);
        $this->assertTrue($updateResponse['complete']);
    }

    public function testUpdateChecklistItem_error_403_otherUser()
    {
        // create checklist item with currently logged-in user
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);

        // try to update checklist item with other user
        self::$client->loginUser($this->createUser());
        $this->requestApi('PUT', '/page/section/checklist/item/' . $createResponse['id'], [
            'name' => 'Updated Item',
            'complete' => true,
        ], expectStatusCode: 403);
    }

    public function testUpdateChecklistItem_error_400_tryToUpdateChecklist()
    {
        $createResponse = $this->requestJsonApi('POST', '/page/section/checklist/item', [
            'pageSectionChecklist' => $this->getPageSectionChecklist()->getId(),
            'name' => 'Test Item',
            'complete' => false,
        ]);

        $this->requestApi('PUT', '/page/section/checklist/item/' . $createResponse['id'], [
            'pageSectionChecklist' => 99999,
            'name' => 'Updated Item',
            'complete' => true,
        ], expectStatusCode: 400);
    }

    protected function getPageSectionChecklist(): PageSectionChecklist
    {
        // fetch a managed version of the project entity
        $project = self::$em->getRepository(Project::class)->find(self::$loggedInUser->getSelectedProject()->getId());
        $page = (new Page())
            ->setName('Test Page')
            ->setProject($project)
            ->setCreatedAt(new \DateTime());
        self::$em->persist($page);

        $pageTab = (new PageTab())
            ->setName('Test Tab')
            ->setPage($page)
            ->setCreatedAt(new \DateTime());
        self::$em->persist($pageTab);

        $pageSection = (new PageSection())
            ->setPageTab($pageTab)
            ->setUpdatedAt(new \DateTime())
            ->setCreatedAt(new \DateTime())
            ->setOrderIndex(0)
            ->setAuthor(self::$loggedInUser);
        self::$em->persist($pageSection);

        $pageSectionChecklist = (new PageSectionChecklist())
            ->setName('Test Checklist')
            ->setPageSection($pageSection);
        self::$em->persist($pageSectionChecklist);

        self::$em->flush();

        return $pageSectionChecklist;
    }
}