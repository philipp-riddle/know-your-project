<?php

namespace App\Tests\Unit\Serializer;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\PageSectionText;
use App\Entity\PageTab;
use App\Entity\Tag;
use App\Entity\TagPage;
use App\Entity\User;
use App\Serializer\EntitySerializer;
use App\Tests\TestCase;
use ReflectionClass;

class EntitySerializerTest extends TestCase
{
    public static array $entityClassesToClear = [
        PageSection::class,
        PageTab::class,
        PageSectionText::class,
        Page::class,
        Tag::class,
        TagPage::class,
    ];

    // public function testSerialize_pageSection_depth_0(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageSection, 0);

    //     $this->assertSame($pageSection->getId(), $serialized['id']);
    //     $this->assertSame($pageSection->getPageTab()->getId(), $serialized['pageTab']);
    //     $this->assertIsString($serialized['createdAt']);
    //     $this->assertSame($owner->getId(), $serialized['author']);
    //     $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized['pageSectionText']);
    //     $this->assertNull($serialized['pageSectionChecklist']);
    // }

    // public function testSerialize_pageSection_depth_1(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageSection, 1);

    //     $this->assertSame($pageSection->getId(), $serialized['id']);
    //     $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized['pageSectionText']['id']);
    //     $this->assertSame($pageSection->getId(), $serialized['pageSectionText']['pageSection']);
    //     $this->assertSame($pageSection->getPageSectionText()->getContent(), $serialized['pageSectionText']['content']);

    //     $this->assertSame($owner->getId(), $serialized['author']['id']);
    //     $this->assertSame($owner->getEmail(), $serialized['author']['email']);
    //     $this->assertSame([], $serialized['author']['projectUsers']);

    //     $this->assertSame($pageSection->getPageTab()->getId(), $serialized['pageTab']['id']);
    //     $this->assertSame($pageSection->getPageTab()->getPage()->getId(), $serialized['pageTab']['page']);
    //     $this->assertSame($pageSection->getPageTab()->getName(), $serialized['pageTab']['name']);
    //     $this->assertSame([], $serialized['pageTab']['pageSections']);
    // }

    // public function testSerialize_pageSection_depth_2(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageSection, 2);

    //     $this->assertSame($pageSection->getId(), $serialized['id']);
    //     $this->assertNotEmpty($serialized['pageTab']['pageSections']);
    //     $this->assertCount(1, $serialized['pageTab']['pageSections']);
    //     $this->assertSame($pageSection->getId(), $serialized['pageTab']['pageSections'][0], 'Page Section Circular Reference should only return ID');
    // }

    // public function testSerialize_pageSectionText_depth_5_containsCircularDependencies(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSectionText = $this->getPageSection($owner)->getPageSectionText();
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageSectionText, 2);

    //     $this->assertSame($pageSectionText->getId(), $serialized['id']);
    //     $this->assertEmpty($serialized['pageSection']['pageTab']['pageSections']);
    // }

    // public function testSerialize_pageTab_depth_0(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $pageTab = $pageSection->getPageTab();
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageTab, 0);

    //     $this->assertSame($pageTab->getId(), $serialized['id']);
    //     $this->assertSame($pageTab->getPage()->getId(), $serialized['page']);
    //     $this->assertSame([], $serialized['pageSections']);
    // }

    // public function testSerialize_pageTab_depth_1(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $pageTab = $pageSection->getPageTab();
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageTab, 1);

    //     $this->assertSame($pageTab->getId(), $serialized['id']);
    //     $this->assertSame($pageTab->getPage()->getId(), $serialized['page']['id']);
    //     $this->assertIsArray($serialized['pageSections']);
    //     $this->assertCount(1, $serialized['pageSections']);
    //     $this->assertSame($pageSection->getId(), $serialized['pageSections'][0]['id']);
    //     $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized['pageSections'][0]['pageSectionText']);
    // }

    public function testSerialize_page_withTags(): void
    {
        $owner = $this->createUser();
        $page = $this->getPageSection($owner)->getPageTab()->getPage();
        $tag = (new Tag())
            ->setName('Test Tag')
            ->setProject($owner->getSelectedProject())
            ->initialize();
        $tagPage = (new TagPage())
            ->setTag($tag)
            ->setPage($page);
        $page->addTag($tagPage);

        self::$em->persist($tag);
        self::$em->persist($tagPage);
        self::$em->flush();

        var_dump('tag count', count($page->getTags()));

        $page = self::$em->getRepository(Page::class)->find($page->getId()); // fetch a managed version of the entity
        $serialized = (new EntitySerializer())->serialize($owner, $page, 2);

        $this->assertSame($page->getId(), $serialized['id']);

        die(json_encode($serialized['tags']).PHP_EOL);
    }

    // public function testSerialize_pageTab_emptyPageSections_depth1(): void
    // {
    //     $owner = $this->createUser();
    //     $pageTab = $this->getPageTab($owner);;
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageTab, 1);

    //     $this->assertSame($pageTab->getId(), $serialized['id']);
    //     $this->assertSame([], $serialized['pageSections']);
    // }

    // /**
    //  * Simulates a real-word scenario with a higher depth.
    //  */
    // public function testSerialize_user_depth_5(): void
    // {
    //     $user = $this->createUser();
    //     $serialized = (new EntitySerializer())->serialize($user, $user, 5);

    //     $this->assertSame($user->getId(), $serialized['id']);
    // }

    // public function testSerialize_array_depth_0(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $pageTab = $pageSection->getPageTab();
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageTab->getPageSections(), 0);

    //     $this->assertIsArray($serialized);
    //     $this->assertCount(1, $serialized);
    //     $this->assertSame($pageSection->getId(), $serialized[0]['id']);
    //     $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized[0]['pageSectionText']);
    // }

    // public function testSerialize_pageSections_array_depth_1(): void
    // {
    //     $owner = $this->createUser();
    //     $pageSection = $this->getPageSection($owner);
    //     $pageTab = $pageSection->getPageTab();
    //     $serialized = (new EntitySerializer())->serialize($owner, $pageTab->getPageSections(), 1);

    //     $this->assertIsArray($serialized);
    //     $this->assertCount(1, $serialized);
    //     $this->assertSame($pageSection->getId(), $serialized[0]['id']);
    //     $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized[0]['pageSectionText']);
    // }

    // public function testSerialize_pages_array_excludePageTabs(): void
    // {
    //     $owner = $this->createUser();
    //     $page = $this->getPageSection($owner)->getPageTab()->getPage();
    //     $serialized = (new EntitySerializer())->serialize($owner, [$page], 5);

    //     $this->assertIsArray($serialized);
    //     $this->assertCount(1, $serialized);
    //     $this->assertSame($page->getId(), $serialized[0]['id']);
    //     $this->assertEmpty($serialized[0]['pageTabs']);
    // }

    // public function testGetNestedReflectionClass_pageTab_getPage()
    // {
    //     $user = $this->createUser();
    //     $pageTab = $this->getPageTab($user);
    //     $reflectionMethod = (new ReflectionClass($pageTab))->getMethod('getPage');

    //     $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $pageTab, $reflectionMethod);
    //     $this->assertSame(Page::class, $reflectionClass->getName());
    // }

    // public function testGetNestedReflectionClass_pageTab_getPageSections()
    // {
    //     $user = $this->createUser();
    //     $pageTab = $this->getPageTab($user);
    //     $reflectionMethod = (new ReflectionClass($pageTab))->getMethod('getPageSections');

    //     $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $pageTab, $reflectionMethod);
    //     $this->assertSame(PageSection::class, $reflectionClass->getName());
    // }

    // public function testGetNestedReflectionClass_null_pageTab_initialize()
    // {
    //     $user = $this->createUser();
    //     $pageTab = $this->getPageTab($user);
    //     $reflectionMethod = (new ReflectionClass($pageTab))->getMethod('initialize');

    //     $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $pageTab, $reflectionMethod);
    //     $this->assertNull($reflectionClass);
    // }

    // public function testGetNestedReflectionClass_ignoreWhenNested_user_projectUsers()
    // {
    //     $user = $this->createUser();
    //     $reflectionMethod = (new ReflectionClass($user))->getMethod('getProjectUsers');

    //     $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $user, $reflectionMethod, currentDepth: 1);
    //     $this->assertNull($reflectionClass);

    //     $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $user, $reflectionMethod, currentDepth: 0);
    //     $this->assertNotNull($reflectionClass);
    // }

    // public function testShouldSerializeMethod_pageTab_getPage()
    // {
    //     $reflectionMethod = (new ReflectionClass(PageTab::class))->getMethod('getPage');

    //     $this->assertTrue((new EntitySerializer())->shouldSerializeMethod($reflectionMethod));
    // }

    // public function testShouldSerializeMethod_pageTab_initialize()
    // {
    //     $reflectionMethod = (new ReflectionClass(PageTab::class))->getMethod('initialize');

    //     $this->assertFalse((new EntitySerializer())->shouldSerializeMethod($reflectionMethod));
    // }

    // public function testGetEntityClassFromDocComment_simpleReturn()
    // {
    //     $docComment = '/** * @return User **/';
    //     $this->assertEquals(User::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    // }
    
    // public function testGetEntityClassFromDocComment_arrayReturn()
    // {
    //     $docComment = "** * @return User[] **/";
    //     $this->assertEquals(User::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    // }

    // public function testGetEntityClassFromDocComment_collectionReturn()
    // {
    //     $docComment = '/** * @return Collection<int, PageSection> **/';
    //     $this->assertEquals(PageSection::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    // }


    // public function testGetEntityClassFromDocComment_complexCollectionReturn()
    // {
    //     $docComment = '** @return PageSection[]|Collection<int, PageSection> */';
    //     $this->assertEquals(PageSection::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    // }

    // public function testGetEntityClassFromDocComment_withClassNamespace()
    // {
    //     $docComment = '/** * @return App\Entity\User **/';
    //     $this->assertEquals('App\Entity\User', (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    // }

    // @todo maybe later fix this to be safe; some restructuring of the code is required to accomplish this
    // public function testGetEntityClassFromDocComment_withSelfExpression()
    // {
    //     $docComment = '* @return Collection<int, self>';
    //     $this->assertEquals('App\Entity\User', (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    // }

    private function getPageTab(User $owner, bool $flush = true): PageTab
    {
        $page = (new Page())
        ->setUser($owner)
        ->setProject($owner->getSelectedProject())
        ->setName('Test Page')
        ->setProject($owner->getSelectedProject())
        ->initialize();
        $pageTab = (new PageTab())
            ->setName('Test Page Tab')
            ->initialize();
        $page->addPageTab($pageTab);

        self::$em->persist($page);
        self::$em->persist($pageTab);

        if ($flush) {
            self::$em->flush();
        }

        return $pageTab;
    }

    private function getPageSection(User $owner): PageSection
    {
        $pageSectionText = (new PageSectionText())
            ->setContent('This is a test content');
        $pageSection = (new PageSection())
            ->setAuthor($owner)
            ->setPageSectionText($pageSectionText)
            ->initialize()
            ->setOrderIndex(0);

        $pageTab = $this->getPageTab($owner, flush: false);
        $pageTab->addPageSection($pageSection);

        self::$em->persist($pageSectionText);
        self::$em->persist($pageSection);

        self::$em->flush();

        return $pageSection;
    }
}