<?php

namespace App\Service;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\PageSectionText;
use App\Entity\PageTab;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

class PageService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) { }

    public function createDefaultPage(Task $task): Page
    {
        $page = (new Page())
            ->setProject($task->getProject())
            ->setName('Page for ' . $task->getName())
            ->setCreatedAt(new \DateTime());

        $pageTab = (new PageTab())
            ->setName('Overview')
            ->setEmojiIcon('🌐')
            ->setCreatedAt(new \DateTime());
        $page->addPageTab($pageTab);

        $pageSection = (new PageSection())
            ->setAuthor($task->getProject()->getOwner())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedAt(new \DateTime())
            ->setOrderIndex(0);
        $pageSectionText = (new PageSectionText())
            ->setContent('This is the overview section for the task ' . $task->getName());
        $pageSection->setPageSectionText($pageSectionText);
        $pageTab->addPageSection($pageSection);

        $this->em->persist($page);
        $this->em->persist($pageTab);
        $this->em->persist($pageSection);

        return $page;
    }
}