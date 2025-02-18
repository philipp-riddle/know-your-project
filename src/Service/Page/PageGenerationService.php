<?php

namespace App\Service\Page;

use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionChecklist;
use App\Entity\Page\PageSectionChecklistItem;
use App\Entity\Page\PageSectionText;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagPage;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PageGenerationService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) { }

    public function isPageEmpty(Page $page): bool
    {
        if (0 === count($page->getPageTabs())) {
            return true;
        }

        $pageTab = $page->getPageTabs()[0];

        if (0 === count($pageTab->getPageSections())) {
            return true;
        }

        $pageSection = $pageTab->getPageSections()[0];
        $pageSectionText = $pageSection->getPageSectionText()?->getContent();

        if (null === $pageSectionText) {
            return false; // if the first page section is anyything other than text the page is not empty
        }

        // trim \n and \r from the content
        $content = \trim($pageSectionText);

        return '' === $content || '<p></p>' === $content;
    }

    /**
     * Generates a page with specified options, with the minimum required fields being the user, page, title, and content.
     * 
     * @param User $user The user creating the page.
     * @param Page $page The page to generate the content for.
     * @param string $title The title of the page.
     * @param string $content The content of the page.
     * @param Tag|null $tag The tag to associate with the page; if not given the page remains uncategorised.
     * @param string[] $checklistItemNames The checklist item names to associate with the page; this method automatically translates this into a checklist.
     */
    public function generatePage(User $user, Page $page, string $title, string $content, ?Tag $tag = null, array $checklistItemNames = []): Page
    {
        if (!$this->isPageEmpty($page)) {
            throw new BadRequestHttpException('The page is not empty; cannot generate contents for it.');
        }

        $pageTab = $page->getPageTabs()[0];

        // remove the existing sections to replace them with the new content
        foreach ($pageTab->getPageSections() as $pageSection) {
            $pageTab->removePageSection($pageSection);
            $this->em->remove($pageSection);
        }

        // if a tag was specified we must also clear all the tags
        if (null !== $tag) {
            foreach ($page->getTags() as $tagPage) {
                $page->removeTag($tagPage);
                $this->em->remove($tagPage);
            }
        }

        $this->em->flush();

        // assign the new page name
        $page->setName($title);

        // create the page section text with the rest of the content
        $pageSection = (new PageSection())
            ->setPageTab($pageTab)
            ->setOrderIndex(0)
            ->setAuthor($user)
            ->initialize();
        $pageSectionText = (new PageSectionText())
            ->setContent($content);
        $pageSection->setPageSectionText($pageSectionText);
        $pageTab->addPageSection($pageSection);

        $this->em->persist($pageSection);
        $this->em->flush();

        if (count($checklistItemNames) > 0) {
            $checklist = (new PageSectionChecklist())
                ->setName('Checklist');
            $this->em->persist($checklist);
            
            foreach ($checklistItemNames as $checklistItemName) {
                $checklistItem = (new PageSectionChecklistItem())
                    ->setName($checklistItemName)
                    ->setComplete(false);
                $checklist->addPageSectionChecklistItem($checklistItem);
                $this->em->persist($checklistItem);
            }

            $pageSection = (new PageSection())
                ->setPageTab($pageTab)
                ->setOrderIndex(1)
                ->setAuthor($user)
                ->initialize()
                ->setPageSectionChecklist($checklist);
            $pageTab->addPageSection($pageSection);
            $this->em->persist($pageSection);
        }

        $this->em->flush();

        if (null !== $tag) {
            $tagPage = (new TagPage())
                ->setTag($tag);
            $page->addTag($tagPage);
            $this->em->persist($tagPage);
        }

        $this->em->persist($pageSection);
        $this->em->flush();

        return $page;
    }
}