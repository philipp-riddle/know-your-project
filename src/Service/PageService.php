<?php

namespace App\Service;

use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionText;
use App\Entity\Page\PageTab;
use App\Entity\Project\Project;
use App\Entity\Task;
use App\Entity\User\User;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

class PageService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PageRepository $pageRepository,
    ) { }

    public function createDefaultTaskPage(User $user, Task $task, string $pageName): Page
    {
        $page = $this
            ->createEmptyPage($user, $task->getProject(), $pageName)
            ->setTask($task);
        $this->em->persist($page);

        $pageSection = (new PageSection())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($task->getProject()->getOwner())
            ->setOrderIndex(0);
        $pageSectionText = (new PageSectionText())
            ->setContent(''); // add empty content for the user to start
        $pageSection->setPageSectionText($pageSectionText);
        $page->getPageTabs()[0]->addPageSection($pageSection);

        $this->em->persist($pageSection);
        $this->em->persist($pageSectionText);

        return $page;
    }

    public function createEmptyPage(User $user, Project $project, string $pageName): Page
    {
        $page = (new Page())
            ->setProject($project)
            ->setUser($user)
            ->setName($pageName)
            ->setCreatedAt(new \DateTime());
        $pageTab = (new PageTab())
            ->setName('Overview')
            ->setEmojiIcon('🌐')
            ->setCreatedAt(new \DateTime());
        $page->addPageTab($pageTab);

        $this->em->persist($page);
        $this->em->persist($pageTab);

        return $page;
    }

    /**
     * @return Page[]
     */
    public function getUntaggedPages(Project $project, int $limit = 25): array
    {
        return $this->pageRepository->findProjectPages($project, tags: [], limit: $limit);
    }
}