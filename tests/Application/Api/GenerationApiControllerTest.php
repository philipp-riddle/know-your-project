<?php

namespace App\Tests\Application\Api;

use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionText;

class GenerationApiControllerTest extends ApiControllerTestCase
{
    public function testAsk_noResults(): void
    {
        $askResponse = $this->requestJsonApi('POST', '/generation/ask/' . self::$loggedInUser->getSelectedProject()->getId(), [
            'question' => 'What is the meaning of life in 3 words?',
        ]);

        $this->assertEmpty($askResponse['searchResults']);
        $this->assertArrayHasKey('answer', $askResponse);
        $this->assertIsArray($askResponse['answer']);
        $this->assertIsString($askResponse['answer']['title']);
        $this->assertIsString($askResponse['answer']['content']);
    }

    public function testSave_newPage(): void
    {
        $saveResponse = $this->requestJsonApi('POST', '/generation/save/' . self::$loggedInUser->getSelectedProject()->getId(), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>',
        ]);

        $this->assertNotNull($saveResponse['id']);
        $this->assertSame('My generated page', $saveResponse['name']);

        $this->assertSame(self::$loggedInUser->getSelectedProject()->getId(), $saveResponse['project']['id']);
        $this->assertSame(self::$loggedInUser->getId(), $saveResponse['user']['id']);
        $this->assertSame(1, count($saveResponse['pageTabs']));
        $this->assertCount(1, $saveResponse['pageTabs'][0]['pageSections']);

        $pageSection = $saveResponse['pageTabs'][0]['pageSections'][0];

        $this->assertSame('<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>', $pageSection['pageSectionText']['content']);
    }

    public function testSave_toEmptyPage(): void
    {
        // first create an empty page
        $pageTab = $this->getPageTab();
        $pageSectionText = (new PageSectionText())
            ->setContent('<p></p>');
        $pageSection = (new PageSection())
            ->setPageSectionText($pageSectionText)
            ->setPageTab($pageTab)
            ->setOrderIndex(0)
            ->initialize();
        self::$em->persist($pageSection);
        self::$em->flush();

        $saveResponse = $this->requestJsonApi('POST', \sprintf('/generation/save/%d/%d', self::$loggedInUser->getSelectedProject()->getId(), $pageTab->getPage()->getId()), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>',
        ]);

        $this->assertSame($pageTab->getPage()->getId(), $saveResponse['id']);
        $this->assertSame('My generated page', $saveResponse['name']);

        $this->assertSame(self::$loggedInUser->getSelectedProject()->getId(), $saveResponse['project']['id']);
        $this->assertSame(self::$loggedInUser->getId(), $saveResponse['user']['id']);
        $this->assertSame(1, count($saveResponse['pageTabs']));
        $this->assertCount(1, $saveResponse['pageTabs'][0]['pageSections']);

        $pageSection = $saveResponse['pageTabs'][0]['pageSections'][0];
        $this->assertSame('<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>', $pageSection['pageSectionText']['content']);
    }

    public function testSave_exception_pageIsNotEmpty()
    {
        // first create a page with content
        $pageTab = $this->getPageTab();
        $pageSectionText = (new PageSectionText())
            ->setContent('<p>Some content</p>');
        $pageSection = (new PageSection())
            ->setPageSectionText($pageSectionText)
            ->setPageTab($pageTab)
            ->setOrderIndex(0)
            ->initialize();
        self::$em->persist($pageSection);
        self::$em->flush();

        $this->requestJsonApi('POST', \sprintf('/generation/save/%d/%d', self::$loggedInUser->getSelectedProject()->getId(), $pageTab->getPage()->getId()), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>',
        ], expectStatusCode: 400);
    }

    public function testSave_exception_toUnauthorizedPage(): void
    {
        // first create an empty page with another user
        $pageTab = $this->getPageTab($this->createUser());
        $pageSectionText = (new PageSectionText())
            ->setContent('<p></p>');
        $pageSection = (new PageSection())
            ->setPageSectionText($pageSectionText)
            ->setPageTab($pageTab)
            ->setOrderIndex(0)
            ->initialize();
        self::$em->persist($pageSection);
        self::$em->flush();

        $this->requestJsonApi('POST', \sprintf('/generation/save/%d/%d', self::$loggedInUser->getSelectedProject()->getId(), $pageTab->getPage()->getId()), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>',
        ], expectStatusCode: 403);
    }

    public function testSave_exception_providedProjectDoesNotMatchPageProject(): void
    {
        // first create an empty page with the currently selected project & logged in user
        $pageTab = $this->getPageTab();
        $pageSectionText = (new PageSectionText())
            ->setContent('<p></p>');
        $pageSection = (new PageSection())
            ->setPageSectionText($pageSectionText)
            ->setPageTab($pageTab)
            ->setOrderIndex(0)
            ->initialize();
        self::$em->persist($pageSection);
        self::$em->flush();

        // create another project with the user and try to use it with the page of the other project
        $secondaryProject = $this->createProject(self::$loggedInUser);

        $this->requestJsonApi('POST', \sprintf('/generation/save/%d/%d', $secondaryProject->getId(), $pageTab->getPage()->getId()), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>',
        ], expectStatusCode: 400);
    }

    public function testSave_exception_containsXSSCode(): void
    {
        $this->requestJsonApi('POST', '/generation/save/' . self::$loggedInUser->getSelectedProject()->getId(), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><div onmouseover="alert(\'XSS\')">Hover me</div>',
        ], expectStatusCode: 400);
    }

    public function testSave_exception_unauthorizedProject(): void
    {
        $user2 = $this->createUser();

        $this->requestJsonApi('POST', '/generation/save/' . $user2->getSelectedProject()->getId(), [
            'title' => '</p>My generated page</p>',
            'content' => '<p>My generated content</p><ul><li>Item 1</li><li>Item 2</li></ul>',
        ], expectStatusCode: 403);
    }
}