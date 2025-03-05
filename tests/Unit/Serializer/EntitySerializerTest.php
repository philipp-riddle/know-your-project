<?php

namespace App\Tests\Unit\Serializer;

use App\Entity\File;
use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionChecklistItem;
use App\Entity\Page\PageSectionText;
use App\Entity\Page\PageSectionURL;
use App\Entity\Page\PageTab;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagPage;
use App\Entity\User\User;
use App\Entity\User\UserInvitation;
use App\Serializer\EntitySerializer;
use App\Serializer\SerializerContext;
use App\Tests\Application\ApplicationTestCase;
use ReflectionClass;

class EntitySerializerTest extends ApplicationTestCase
{
    public static array $entityClassesToClear = [
        PageSection::class,
        PageTab::class,
        PageSectionText::class,
        Page::class,
        Tag::class,
        TagPage::class,
    ];

    public function testSerialize_pageSection_depth_0(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $serialized = (new EntitySerializer())->serialize($owner, $pageSection, 0);

        $this->assertSame($pageSection->getId(), $serialized['id']);
        $this->assertSame($pageSection->getPageTab()->getId(), $serialized['pageTab']);
        $this->assertIsString($serialized['createdAt']);
        $this->assertSame($owner->getId(), $serialized['author']);
        $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized['pageSectionText']);
        $this->assertNull($serialized['pageSectionChecklist']);
    }

    public function testSerialize_pageSection_depth_1(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $serialized = (new EntitySerializer())->serialize($owner, $pageSection, 1);

        $this->assertSame($pageSection->getId(), $serialized['id']);
        $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized['pageSectionText']['id']);
        $this->assertSame($pageSection->getId(), $serialized['pageSectionText']['pageSection']);
        $this->assertSame($pageSection->getPageSectionText()->getContent(), $serialized['pageSectionText']['content']);

        $this->assertSame($owner->getId(), $serialized['author']['id']);
        $this->assertSame($owner->getEmail(), $serialized['author']['email']);
        $this->assertSame([], $serialized['author']['projectUsers']);

        $this->assertSame($pageSection->getPageTab()->getId(), $serialized['pageTab']['id']);
        $this->assertSame($pageSection->getPageTab()->getPage()->getId(), $serialized['pageTab']['page']);
        $this->assertSame($pageSection->getPageTab()->getName(), $serialized['pageTab']['name']);
        $this->assertSame([], $serialized['pageTab']['pageSections']);
    }

    public function testSerialize_pageSection_depth_2(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $serialized = (new EntitySerializer())->serialize($owner, $pageSection, 2);

        $this->assertSame($pageSection->getId(), $serialized['id']);
        $this->assertNotEmpty($serialized['pageTab']['pageSections']);
        $this->assertCount(1, $serialized['pageTab']['pageSections']);
        $this->assertSame($pageSection->getId(), $serialized['pageTab']['pageSections'][0], 'Page Section Circular Reference should only return ID');
    }

    public function testSerialize_pageSectionText_depth_5_containsCircularDependencies(): void
    {
        $owner = $this->createUser();
        $pageSectionText = $this->getPageSection($owner)->getPageSectionText();
        $serialized = (new EntitySerializer())->serialize($owner, $pageSectionText, 2);

        $this->assertSame($pageSectionText->getId(), $serialized['id']);
        $this->assertEmpty($serialized['pageSection']['pageTab']['pageSections']);
    }

    public function testSerialize_pageTab_depth_0(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $pageTab = $pageSection->getPageTab();
        $serialized = (new EntitySerializer())->serialize($owner, $pageTab, 0);

        $this->assertSame($pageTab->getId(), $serialized['id']);
        $this->assertSame($pageTab->getPage()->getId(), $serialized['page']);
        $this->assertSame([], $serialized['pageSections']);
    }

    public function testSerialize_pageTab_depth_1(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $pageTab = $pageSection->getPageTab();
        $serialized = (new EntitySerializer())->serialize($owner, $pageTab, 1);

        $this->assertSame($pageTab->getId(), $serialized['id']);
        $this->assertSame($pageTab->getPage()->getId(), $serialized['page']['id']);
        $this->assertIsArray($serialized['pageSections']);
        $this->assertCount(1, $serialized['pageSections']);
        $this->assertSame($pageSection->getId(), $serialized['pageSections'][0]['id']);
        $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized['pageSections'][0]['pageSectionText']);
    }

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

        $tag2 = (new Tag())
            ->setName('Test Tag 2')
            ->setProject($owner->getSelectedProject())
            ->initialize();
        $tagPage2 = (new TagPage())
            ->setTag($tag2)
            ->setPage($page);
        $page->addTag($tagPage2);

        self::$em->persist($tag);
        self::$em->persist($tag2);
        self::$em->persist($tagPage);
        self::$em->persist($tagPage2);
        self::$em->flush();

        $page = self::$em->getRepository(Page::class)->find($page->getId()); // fetch a managed version of the entity
        $serialized = (new EntitySerializer())->serialize($owner, $page, 2);

        $this->assertSame($page->getId(), $serialized['id']);

        $this->assertCount(2, $serialized['tags']);
        $this->assertIsInt($serialized['tags'][0]['id']);
        $this->assertIsInt($serialized['tags'][1]['id']);
    }

    public function testSerialize_project_withTags(): void
    {
        $owner = $this->createUser();
        $project = $owner->getSelectedProject();
        $tag = (new Tag())
            ->setName('Test Tag')
            ->setProject($project)
            ->initialize();
        $project->addTag($tag);
        $tag2 = (new Tag())
            ->setName('Test Tag 2')
            ->setProject($project)
            ->initialize();
        $project->addTag($tag2);

        self::$em->persist($tag);
        self::$em->persist($tag2);
        self::$em->flush();

        $project = self::$em->getRepository(Project::class)->find($project->getId()); // fetch a managed version of the entity
        $serialized = (new EntitySerializer())->serialize($owner, $project, 2);

        $this->assertSame($project->getId(), $serialized['id']);

        $this->assertCount(2, $serialized['tags']);
        $this->assertIsInt($serialized['tags'][0]['id']);
        $this->assertIsInt($serialized['tags'][1]['id']);
    }

    public function testSerialize_pageTab_emptyPageSections_depth1(): void
    {
        $owner = $this->createUser();
        $pageTab = $this->getPageTab($owner);
        $serialized = (new EntitySerializer())->serialize($owner, $pageTab, 1);

        $this->assertSame($pageTab->getId(), $serialized['id']);
        $this->assertSame([], $serialized['pageSections']);
    }

    /**
     * Simulates a real-word scenario with a higher depth.
     */
    public function testSerialize_user_depth_5(): void
    {
        $user = $this->createUser();
        $serialized = (new EntitySerializer())->serialize($user, $user, 5);

        $this->assertSame($user->getId(), $serialized['id']);
        $this->assertNull($serialized['password'], 'PASSWORD MUST BE NULL IN THE SERIALIZED OBJECT!');
    }

    public function testSerialize_array_depth_0(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $pageTab = $pageSection->getPageTab();
        $serialized = (new EntitySerializer())->serialize($owner, $pageTab->getPageSections(), 0);

        $this->assertIsArray($serialized);
        $this->assertCount(1, $serialized);
        $this->assertSame($pageSection->getId(), $serialized[0]['id']);
        $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized[0]['pageSectionText']);
    }

    public function testSerialize_pageSections_array_depth_1(): void
    {
        $owner = $this->createUser();
        $pageSection = $this->getPageSection($owner);
        $pageTab = $pageSection->getPageTab();
        $serialized = (new EntitySerializer())->serialize($owner, $pageTab->getPageSections(), 1);

        $this->assertIsArray($serialized);
        $this->assertCount(1, $serialized);
        $this->assertSame($pageSection->getId(), $serialized[0]['id']);
        $this->assertSame($pageSection->getPageSectionText()->getId(), $serialized[0]['pageSectionText']);
    }

    public function testSerialize_pages_array_excludePageTabs(): void
    {
        $owner = $this->createUser();
        $page = $this->getPageSection($owner)->getPageTab()->getPage();
        $serialized = (new EntitySerializer())->serialize($owner, [$page], 5);

        $this->assertIsArray($serialized);
        $this->assertCount(1, $serialized);
        $this->assertSame($page->getId(), $serialized[0]['id']);
        $this->assertEmpty($serialized[0]['pageTabs']);
    }

    /**
     * Edge case: User gets invited to the project, thus allowed to read the project's data.
     */
    public function testSerialize_userInvitation_withCorrectContext(): void
    {
        $user = $this->createUser();
        $user2 = $this->createUser();

        // create an invitation for the 2nd user to the project of the 1st user
        $userInvitation = (new UserInvitation())
            ->setUser($user2)
            ->setEmail('kyp@kyp.kyp')
            ->setCode('aaaa')
            ->setProject($user->getSelectedProject())
            ->initialize();
        self::$em->persist($userInvitation);
        self::$em->flush();

        $serialized = (new EntitySerializer())->serialize($user2, $userInvitation, 5, SerializerContext::INVITATION);

        $this->assertIsInt($serialized['id']);
        $this->assertNotEmpty($serialized['project']);
        $this->assertIsInt($serialized['project']['id']);
    }

    public function testSerialize_userInvitation_withDefaultContext(): void
    {
        $user = $this->createUser();
        $user2 = $this->createUser();

        // create an invitation for the 2nd user to the project of the 1st user
        $userInvitation = (new UserInvitation())
            ->setUser($user2)
            ->setEmail('kyp@kyp.kyp')
            ->setCode('aaaa')
            ->setProject($user->getSelectedProject())
            ->initialize();
        self::$em->persist($userInvitation);
        self::$em->flush();

        $serialized = (new EntitySerializer())->serialize($user2, $userInvitation, 5);

        $this->assertIsInt($serialized['id']);
        $this->assertIsInt($serialized['project']);
    }

    /**
     * Edge case: User gets invited to the project, but has another project the other user has no access to.
     */
    public function testSerialize_user_hasOwnProjects_accessBySecondUser(): void
    {
        $user = $this->createUser();
        $user2 = $this->createUser();

        // add the 2nd user to the project of the 1st user
        $projectUser = (new ProjectUser())
            ->setUser($user2)
            ->setProject($user->getSelectedProject())
            ->initialize();
        $user->getSelectedProject()->addProjectUser($projectUser);
        $user2->addProjectUser($projectUser);
        self::$em->persist($projectUser);
        self::$em->flush();

        $serialized = (new EntitySerializer())->serialize($user, $user2, 5);

        $this->assertIsInt($serialized['id']);
        $this->assertCount(1, $serialized['projectUsers']);
        $this->assertSame($user->getSelectedProject()->getId(), $serialized['projectUsers'][0]['project']['id']);
    }

    public function testGetNestedReflectionClass_pageTab_getPage()
    {
        $user = $this->createUser();
        $pageTab = $this->getPageTab($user);
        $reflectionMethod = (new ReflectionClass($pageTab))->getMethod('getPage');

        $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $pageTab, $reflectionMethod);
        $this->assertSame(Page::class, $reflectionClass->getName());
    }

    public function testGetNestedReflectionClass_pageTab_getPageSections()
    {
        $user = $this->createUser();
        $pageTab = $this->getPageTab($user);
        $reflectionMethod = (new ReflectionClass($pageTab))->getMethod('getPageSections');

        $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $pageTab, $reflectionMethod);
        $this->assertSame(PageSection::class, $reflectionClass->getName());
    }

    public function testGetNestedReflectionClass_null_pageTab_initialize()
    {
        $user = $this->createUser();
        $pageTab = $this->getPageTab($user);
        $reflectionMethod = (new ReflectionClass($pageTab))->getMethod('initialize');

        $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $pageTab, $reflectionMethod);
        $this->assertNull($reflectionClass);
    }

    public function testGetNestedReflectionClass_ignoreWhenNested_user_projectUsers()
    {
        $user = $this->createUser();
        $reflectionMethod = (new ReflectionClass($user))->getMethod('getProjectUsers');

        $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $user, $reflectionMethod, currentDepth: 1);
        $this->assertNull($reflectionClass);

        $reflectionClass = (new EntitySerializer())->getNestedReflectionClass($user, $user, $reflectionMethod, currentDepth: 0);
        $this->assertNotNull($reflectionClass);
    }

    public function testGetPropertyNameFromMethod_pageTab_getPage()
    {
        $reflectionMethod = (new ReflectionClass(PageTab::class))->getMethod('getPage');

        $this->assertSame('page', (new EntitySerializer())->getPropertyNameFromMethod($reflectionMethod));
    }

    public function testGetPropertyNameFromMethod_pageSectionChecklistItem_isComplete()
    {
        $reflectionMethod = (new ReflectionClass(PageSectionChecklistItem::class))->getMethod('isComplete');

        $this->assertSame('complete', (new EntitySerializer())->getPropertyNameFromMethod($reflectionMethod));
    }

    public function testGetPropertyNameFromMethod_pageTab_initialize()
    {
        $reflectionMethod = (new ReflectionClass(PageTab::class))->getMethod('initialize');

        $this->assertNull((new EntitySerializer())->getPropertyNameFromMethod($reflectionMethod));
    }

    public function testGetEntityClassFromDocComment_simpleReturn()
    {
        $docComment = '/** * @return User **/';
        $this->assertEquals(User::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    }
    
    public function testGetEntityClassFromDocComment_arrayReturn()
    {
        $docComment = "** * @return User[] **/";
        $this->assertEquals(User::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    }

    public function testGetEntityClassFromDocComment_useEntityInRootEntityDir()
    {
        $docComment = "** * @return File **/";
        $this->assertEquals(File::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    }

    public function testGetEntityClassFromDocComment_collectionReturn()
    {
        $docComment = '/** * @return Collection<int, PageSection> **/';
        $this->assertEquals(PageSection::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    }


    public function testGetEntityClassFromDocComment_complexCollectionReturn()
    {
        $docComment = '** @return PageSection[]|Collection<int, PageSection> */';
        $this->assertEquals(PageSection::class, (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    }

    public function testGetEntityClassFromDocComment_withClassNamespace()
    {
        $docComment = '/** * @return App\Entity\User\User **/';
        $this->assertEquals('App\Entity\User\User', (new EntitySerializer())->getEntityClassFromDocComment($docComment));
    }

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