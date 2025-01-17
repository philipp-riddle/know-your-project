<?php

namespace App\Tests\Unit\Serializer;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\PageSectionText;
use App\Entity\PageTab;
use App\Entity\Project;
use App\Entity\User;
use App\Serializer\NormalizeDepthHandler;
use App\Service\Helper\DefaultNormalizer;
use PHPUnit\Framework\TestCase;

class NormalizeMaxDepthHandlerTest extends TestCase
{
    public function testGetRecursiveMaxDepthPropertiesAndClasses(): void
    {
        $this->markTestSkipped('This test is not yet implemented');
        return;
        $pageSectionText = (new PageSectionText())
            ->setContent('This is a test content');
        $pageSection = (new PageSection())
            ->setPageSectionText($pageSectionText)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
        $pageTab = (new PageTab())
            ->setCreatedAt(new \DateTime());
        $project = (new Project())
            ->setOwner(new User());
        $page = (new Page())
            ->addPageTab($pageTab)
            ->setProject($project);

        $pageSection->setPageTab($pageTab);

        $handler = new NormalizeDepthHandler();
        $maxPropertiesAndClassNames = $handler->getRecursiveMaxDepthPropertiesAndClasses($pageSection, 0);

        // $callbackFunctions = $handler->generateNormalizeCallbacks(PageSection::class, 1);
        // $maxPropertiesAndClassNames = $handler->getRecursiveMaxDepthPropertiesAndClasses($pageSection, 1);

        // $normalizer = new DefaultNormalizer($handler);
        // $normalizedPageSection = $normalizer->normalize($pageSection, $callbackFunctions);

        var_dump($maxPropertiesAndClassNames);
        die();

        // $this->assertEquals([
        //     'pageTab',
        //     'pageTab.page',
        //     'pageTab.page.project',
        // ], $callbacks);
    }
}