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


    public function testCreatePageSection_text_exception_containsScript(): void
    {
        $pageTab = $this->getPageTab();

        $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text <script>alert("XSS");</script>',
            ],
        ], expectStatusCode: 400);
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
        $createdPageResponse2 = $this->requestJsonApi('POST', '/page', [
            'name' => 'Test Page 2',
            'project' => self::$loggedInUser->getSelectedProject()->getId(),
        ]);
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $createdPageResponse['pageTabs'][0]['id'], // embed the second page into the first page
            'embeddedPage' => [
                'page' => $createdPageResponse2['id'],
            ],
        ]);

        $this->assertNotNull($createdSectionResponse['id']);
        $this->assertNull($createdSectionResponse['pageSectionText']);
        $this->assertNull($createdSectionResponse['pageSectionUpload']);
        $this->assertNull($createdSectionResponse['pageSectionChecklist']);

        $this->assertNotNull($createdSectionResponse['embeddedPage']);
        $this->assertSame($createdPageResponse2['id'], $createdSectionResponse['embeddedPage']['page']['id']);
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

    public function testCreatePageSection_url_bootstrapBreakpointDocs(): void
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionURL' => [
                'url' => 'https://getbootstrap.com/docs/5.0/layout/breakpoints/',
            ],
        ]);

        $this->assertNotNull($createdSectionResponse['id']);
        $this->assertNotNull($createdSectionResponse['pageSectionURL']);
        $this->assertSame($createdSectionResponse['pageSectionURL']['url'], 'https://getbootstrap.com/docs/5.0/layout/breakpoints/');
        $this->assertNotNull($createdSectionResponse['pageSectionURL']['name']);
        $this->assertStringContainsString('Breakpoints', $createdSectionResponse['pageSectionURL']['name']);
        $this->assertNotNull($createdSectionResponse['pageSectionURL']['description']);
        $this->assertNotNull($createdSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertStringContainsString('https://getbootstrap.com', $createdSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertNotNull($createdSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertStringContainsString('https://getbootstrap.com', $createdSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertTrue($createdSectionResponse['pageSectionURL']['isInitialized']);
    }

    public function testCreatePageSection_url_unreachable_localhost(): void
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionURL' => [
                'url' => 'https://example-kyp.com/hello-world',
            ],
        ]);

        $this->assertNotNull($createdSectionResponse['id']);
        $this->assertNotNull($createdSectionResponse['pageSectionURL']);
        $this->assertSame('https://example-kyp.com/hello-world', $createdSectionResponse['pageSectionURL']['url']);
        $this->assertSame('URL', $createdSectionResponse['pageSectionURL']['name']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['description']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertTrue($createdSectionResponse['pageSectionURL']['isInitialized']);
    }

    public function testCreatePageSection_url_emptyInitialisation(): void
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionURL' => [
                'url' => '',
            ],
        ]);

        $this->assertNotNull($createdSectionResponse['id']);
        $this->assertNotNull($createdSectionResponse['pageSectionURL']);
        $this->assertSame('', $createdSectionResponse['pageSectionURL']['url']);
        $this->assertSame('', $createdSectionResponse['pageSectionURL']['name']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['description']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertNull($createdSectionResponse['pageSectionURL']['isInitialized']);
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

    public function testCreatePageSection_aiPrompt_initialiseEmpty(): void
    {
        $pageTab = $this->getPageTab();
        $createResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'aiPrompt' => [
                'prompt' => [
                    'promptText' => '',
                ],
            ],
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertStringContainsString('', $createResponse['aiPrompt']['prompt']['promptText']);
        $this->assertNotNull('Hello', $createResponse['aiPrompt']['prompt']['id']);
        $this->assertNull($createResponse['aiPrompt']['prompt']['responseText']);
        $this->assertNull($createResponse['pageSectionUpload']);
        $this->assertNull($createResponse['pageSectionChecklist']);
        $this->assertNull($createResponse['embeddedPage']);

        $this->assertSame($createResponse['author']['id'], self::$loggedInUser->getId());
    }

    public function testCreatePageSection_aiPrompt(): void
    {
        $pageTab = $this->getPageTab();
        $createResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'aiPrompt' => [
                'prompt' => [
                    'promptText' => 'Respond with hello',
                ],
            ],
        ]);

        $this->assertNotNull($createResponse['id']);
        $this->assertStringContainsString('Respond with hello', $createResponse['aiPrompt']['prompt']['promptText']);
        $this->assertNotNull('Hello', $createResponse['aiPrompt']['prompt']['id']);
        $this->assertStringContainsString('Hello', $createResponse['aiPrompt']['prompt']['responseText']);
        $this->assertNull($createResponse['pageSectionUpload']);
        $this->assertNull($createResponse['pageSectionChecklist']);
        $this->assertNull($createResponse['embeddedPage']);

        $this->assertSame($createResponse['author']['id'], self::$loggedInUser->getId());
    }

    public function testCreatePageSection_summary(): void
    {
        $pageTab = $this->getPageTab();

        // first, create page section with content
        $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'If asked for a summary respond with "Know Your Project"',
            ],
        ]);

        // then, create the summary section
        $createSummaryResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionSummary' => [
                'prompt' => [
                    'promptText' => '',
                ],
            ],
        ]);

        $this->assertNotNull($createSummaryResponse['id']);
        $this->assertStringStartsWith('Here is the HTML content I want to summarize:', $createSummaryResponse['pageSectionSummary']['prompt']['promptText']);
        $this->assertNotNull($createSummaryResponse['pageSectionSummary']['prompt']['id']);
        $this->assertStringContainsString('Know Your Project', $createSummaryResponse['pageSectionSummary']['prompt']['responseText']);
        $this->assertNull($createSummaryResponse['pageSectionUpload']);
        $this->assertNull($createSummaryResponse['pageSectionChecklist']);
        $this->assertNull($createSummaryResponse['embeddedPage']);

        $this->assertSame($createSummaryResponse['author']['id'], self::$loggedInUser->getId());
    }

    public function testUpdatePageSection_exception_isText_supplyURLParams(): void
    {
        $pageTab = $this->getPageTab();

        $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionChecklist' => [
                'name' => 'My new checklist',
                'pageSectionURL' => [
                    [
                        'url' => 'https://google.de',
                        'name' => 'Google',
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

    public function testUpdatePageSection_text_exception_containsScriptTag(): void
    {
        $pageTab = $this->getPageTab();

        $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionText' => [
                'content' => 'Test Text <script>alert("XSS");</script>',
            ],
        ], expectStatusCode: 400);
    }

    public function testUpdatePageSection_url_validUrl_bootstrapBreakpointDocs(): void
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionURL' => [
                'url' => '',
            ],
        ]);

        $updateSectionResponse = $this->requestJsonApi('PUT', '/page/section/' . $createdSectionResponse['id'], [
            'pageSectionURL' => [
                'url' => 'https://getbootstrap.com/docs/5.0/layout/breakpoints/',
                'name' => 'Breakpoints',
            ],
        ]);

        $this->assertNotNull($updateSectionResponse['id']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']);
        $this->assertSame($updateSectionResponse['pageSectionURL']['url'], 'https://getbootstrap.com/docs/5.0/layout/breakpoints/');
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['name']);
        $this->assertStringContainsString('Breakpoints', $updateSectionResponse['pageSectionURL']['name']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['description']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertStringContainsString('https://getbootstrap.com', $updateSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertStringContainsString('https://getbootstrap.com', $updateSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertTrue($updateSectionResponse['pageSectionURL']['isInitialized']);
    }

    public function testUpdatePageSection_url_overrideUrlAndName(): void
    {
        $pageTab = $this->getPageTab();
        $createdSectionResponse = $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionURL' => [
                'url' => 'https://example-kyp.com',
            ],
        ]);

        $updateSectionResponse = $this->requestJsonApi('PUT', '/page/section/' . $createdSectionResponse['id'], [
            'pageSectionURL' => [
                'url' => 'https://getbootstrap.com/docs/5.0/layout/breakpoints/',
                'name' => 'Overridden Name',
                'description' => 'My description!',
            ],
        ]);

        $this->assertNotNull($updateSectionResponse['id']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']);
        $this->assertSame('https://getbootstrap.com/docs/5.0/layout/breakpoints/', $updateSectionResponse['pageSectionURL']['url']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['name']);
        $this->assertStringContainsString('Overridden Name', $updateSectionResponse['pageSectionURL']['name']);
        $this->assertSame('My description!', $updateSectionResponse['pageSectionURL']['description']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertStringContainsString('https://getbootstrap.com', $updateSectionResponse['pageSectionURL']['coverImageUrl']);
        $this->assertNotNull($updateSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertStringContainsString('https://getbootstrap.com', $updateSectionResponse['pageSectionURL']['faviconUrl']);
        $this->assertTrue($updateSectionResponse['pageSectionURL']['isInitialized']);
    }

    public function testUpdatePageSection_url_exception_invalidUrl(): void
    {
        $pageTab = $this->getPageTab();
        
        $this->requestJsonApi('POST', '/page/section', [
            'pageTab' => $pageTab->getId(),
            'pageSectionURL' => [
                'url' => 'NO URL',
            ],
        ], expectStatusCode: 400);
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

        $this->assertSame(0, $response[0]['orderIndex']);
        $this->assertSame(1, $response[1]['orderIndex']);
    }
}