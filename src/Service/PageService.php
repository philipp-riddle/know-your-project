<?php

namespace App\Service;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\PageSectionText;
use App\Entity\PageTab;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

class PageService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PageRepository $pageRepository,
    ) { }

    public function createDefaultPage(Task $task, string $pageName): Page
    {
        $page = (new Page())
            ->setProject($task->getProject())
            ->setName($pageName)
            ->setCreatedAt(new \DateTime());
        $pageTab = (new PageTab())
            ->setName('Overview')
            ->setEmojiIcon('🌐')
            ->setCreatedAt(new \DateTime());
        $pageSection = (new PageSection())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($task->getProject()->getOwner())
            ->setOrderIndex(0);
        $pageSectionText = (new PageSectionText())
            ->setContent(''); // add empty content for the user to start
        $pageSection->setPageSectionText($pageSectionText);

        $pageTab->addPageSection($pageSection);
        $page->addPageTab($pageTab);

        $this->em->persist($page);
        $this->em->persist($pageTab);
        $this->em->persist($pageSection);
        $this->em->persist($pageSectionText);

        return $page;
    }

    /**
     * @return Page[]
     */
    public function getUntaggedPages(User $user, Project $project, int $limit = 25): array
    {
        return $this->pageRepository->findProjectPages($user, $project, tags: [], limit: $limit);
    }
}