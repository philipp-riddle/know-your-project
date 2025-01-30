<?php

namespace App\Tests\Application\Api;

use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionChecklist;
use App\Entity\Page\PageSectionChecklistItem;
use App\Entity\Page\PageSectionEmbeddedPage;
use App\Entity\Page\PageSectionText;
use App\Entity\Page\PageSectionUpload;
use App\Entity\Page\PageTab;
use App\Entity\Project\Project;
use App\Entity\User\User;

class PageSectionApiControllerTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        Page::class,
        PageTab::class,
        PageSection::class,
        PageSectionChecklist::class,
        PageSectionChecklistItem::class,
        PageSectionText::class,
        PageSectionEmbeddedPage::class,
        PageSectionUpload::class,
    ];

    public function testCreatePageSection_text(): void
    {
        $pageTab = $this->getPageTab();
        $createResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertSame($createResponse['pageSectionText']['content'], 'Test Text');
        $this->assertNull($createResponse['pageSectionUpload']);
        $this->assertNull($createResponse['pageSectionChecklist']);
        $this->assertNull($createResponse['embeddedPage']);

        $this->assertSame($createResponse['author']['id'], self::$loggedInUser->getId());
    }

    public function testCreatePageSection_text_error_noPageTabSpecified(): void
    {
        $this->requestApi('POST', '/page/section', [
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ], expectStatusCode: 400);
    }

    public function testCreatePageSection_text_error_invalidTabSpecifiedNoAccess(): void
    {
        $pageTab = $this->getPageTab($this->createUser());
        $this->requestApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ], expectStatusCode: 403);
    }

    public function testCreatePageSection_checklist(): void
    {
        $pageTab = $this->getPageTab();
        $createResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionChecklist' => [
                'name' => 'My new checklist',
                'pageSectionChecklistItems' => [
                    [
                        'name' => 'Test Item 1',
                        'complete' => false,
                    ],
                ],
            ],
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertNull($createResponse['pageSectionText']);
        $this->assertNull($createResponse['pageSectionUpload']);
        $this->assertNull($createResponse['embeddedPage']);

        $this->assertNotNull($createResponse['pageSectionChecklist']);
        $this->assertSame($createResponse['pageSectionChecklist']['name'], 'My new checklist');
        $this->assertCount(1, $createResponse['pageSectionChecklist']['pageSectionChecklistItems']);
        $this->assertSame($createResponse['pageSectionChecklist']['pageSectionChecklistItems'][0]['name'], 'Test Item 1');
        $this->assertFalse($createResponse['pageSectionChecklist']['pageSectionChecklistItems'][0]['complete']);
    }

    public function testCreatePageSection_embeddedPage(): void
    {
        $createdPageResponse = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'embeddedPage' => [
                'page' => $createdPageResponse['id'],
            ],
        ]);

        $this->assertNotNull($createdSectionResponse['id']);
        $this->assertNull($createdSectionResponse['pageSectionText']);
        $this->assertNull($createdSectionResponse['pageSectionUpload']);
        $this->assertNull($createdSectionResponse['pageSectionChecklist']);

        $this->assertNotNull($createdSectionResponse['embeddedPage']);
        $this->assertSame($createdSectionResponse['embeddedPage']['page']['id'], $createdPageResponse['id']);
    }

    public function testCreatePageSection_embeddedPage_error_400_cannotEmbedItself(): void
    {
        $pageTab = $this->getPageTab();

        $this->requestApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'embeddedPage' => [
                'page' => $pageTab->getPage()->getId(),
            ],
        ], expectStatusCode: 400);
    }

    public function testCreatePageSection_embeddedPage_error_403_invalidPageNoAccess(): void
    {
        $createdPageTab = $this->getPageTab();
        $invalidPageTab = $this->getPageTab($this->createUser());

        $this->requestApi('POST', '/page/section', [
            'pageTab' => $createdPageTab->getId(),
            'embeddedPage' => [
                'page' => $invalidPageTab->getPage()->getId(),
            ],
        ], expectStatusCode: 403);
    }

    public function testCreatePageSection_error_noType(): void
    {
        $pageTab = $this->getPageTab();

        $this->requestApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
        ], expectStatusCode: 400);
    }

    public function testCreatePageSection_error_multipleTypesAtOnce(): void
    {
        $pageTab = $this->getPageTab();

        $this->requestApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
            'pageSectionChecklist' => [
                'name' => 'My new checklist',
                'pageSectionChecklistItems' => [
                    [
                        'name' => 'Test Item 1',
                        'complete' => false,
                    ],
                ],
            ],
        ], expectStatusCode: 400);
    }

    public function testUpdatePageSection_text(): void
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        $updateResponse = $this->requestJsonApi('PUT', '/page/section/' . $createdSectionResponse['id'], [
            'pageSectionText' => [
                'content' => 'Updated Text',
            ],
        ]);

        $this->assertSame($updateResponse['pageSectionText']['content'], 'Updated Text');
    }

    public function testUpdatePageSection_error_403_otherUser()
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('PUT', '/page/section/' . $createdSectionResponse['id'], [
            'pageSectionText' => [
                'content' => 'Updated Text',
            ],
        ], expectStatusCode: 403);
    }

    public function testGetPageSection_default()
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        $getResponse = $this->requestJsonApi('GET', '/page/section/' . $createdSectionResponse['id']);
        $this->assertSame($getResponse['id'], $createdSectionResponse['id']);
    }

    public function testGetPageSection_error_403_otherUser()
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('GET', '/page/section/' . $createdSectionResponse['id'], expectStatusCode: 403);
    }

    public function testDeletePageSection_default()
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        $response = $this->requestJsonApi('DELETE', '/page/section/' . $createdSectionResponse['id']);
        $this->assertSame(['success' => true], $response);

        // validate via API if the section is really deleted
        $this->requestApi('GET', '/page/section/' . $createdSectionResponse['id'], expectStatusCode: 404);
    }

    public function testDeletePageSection_error_404()
    {
        $this->requestApi('DELETE', '/page/section/999', expectStatusCode: 404);
    }

    public function testDeletePageSection_error_403_otherUser()
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text',
            ],
        ]);

        self::$client->loginUser($this->createUser());
        $this->requestApi('DELETE', '/page/section/' . $createdSectionResponse['id'], expectStatusCode: 403);
    }

    public function testChangeOrder_default()
    {
        $pageTab = $this->getPageTab();
        $createdSection1Response = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text 1',
            ],
        ]);
        $createdSection2Response = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text 2',
            ],
        ]);

        $response = $this->requestJsonApi('PUT', '/page/section/order/'.$pageTab->getId(), [
            'idOrder' => [$createdSection2Response['id'], $createdSection1Response['id']],
        ]);

        $this->assertSame(1, $response[0]['orderIndex']);
        $this->assertSame(0, $response[1]['orderIndex']);
    }
}