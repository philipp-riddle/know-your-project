<?php

namespace App\Service\Page;

use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionURL;
use App\Entity\User\User;
use App\Service\Helper\HTMLParser;
use App\Service\Helper\HTMLValidator;
use App\Service\Search\GenerationEngine;
use Doctrine\ORM\EntityManagerInterface;

class PageSectionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private GenerationEngine $generationEngine,
    ) { }

    /**
     * Every page section create AND update goes through this method.
     * Here we can execute custom logic that is needed to process an update of a specific page section.
     * E.g. a page section AI prompt block needs to generate a response from the AI.
     */
    public function processUpdate(User $currentUser, PageSection $pageSection): void
    {
        // generate the AI prompt response; this is done by chatting with the AI and using the page contents as context.
        if (null !== $pageSection->getAiPrompt()) {
            $this->generationEngine->generatePageSectionAIPrompt($pageSection->getPageTab()->getPage(), $pageSection->getAiPrompt());

        // generate the AI summary promt response.
        } else if (null !== $pageSection->getPageSectionSummary()) {
            // generate the summary for the page section
            $this->generationEngine->generatePageSummary($currentUser, $pageSection->getPageTab()->getPage(), $pageSection->getPageSectionSummary());
            $this->em->persist($pageSection->getPageSectionSummary()->getPrompt());

        // generate the metadata for a page section URL by fetching the URL contents, extracting the meta tags and populating the necessary entity properties.
        } else if (null !== $pageSection->getPageSectionURL()) {
            $this->generatePageSectionURL($pageSection->getPageSectionURL());

        // validate the HTML content of a free-text block.
        } else if (null !== $pageSection->getPageSectionText()) {
            // if the user is updating a free-text block make sure it does not contain any invalid data, such as scripts, iframes, etc.
            // this can be done by validating the HTML content and can be summarised as XSS protection.
            HTMLValidator::validate($pageSection->getPageSectionText()->getContent());
        }
    }

    /**
     * Generates the metadata for a page section URL by fetching the URL contents, extracting the meta tags and populating the necessary entity properties.
     */
    private function generatePageSectionURL(PageSectionURL $pageSectionURL): void
    {
        // first, validate the URL instance. this saves us the checks here and makes the code cleaner.
        $pageSectionURL->validate();
        $url = \trim($pageSectionURL->getUrl());

        if ('' === $url) {
            return; // ignore initialisation of the URL block
        }

        // no matter what happens next - we mark the page section URL as initialized;
        // this means that we have tried to fetch the URL contents and extract the meta tags
        $pageSectionURL->setInitialized(true);

        try {
            $contents = \file_get_contents($url);
        } catch (\Exception) {
            $pageSectionURL->setName($pageSectionURL->getName() ?? 'URL'); // use this as a fallback; this is something the user can see and change
            return;
        }

        $pageMeta = HTMLParser::extractPageMeta($contents);

        // if the page section URL has no name, we set the title of the page as the name;
        // we do this to avoid overriding the custom title the user has already set.
        if (\in_array($pageSectionURL->getName(), [null, '', 'URL'], true)) {
            $pageSectionURL->setName($pageMeta['title'] ?? $pageSectionURL->getName());
        }

        // only override the description if it is not set; this is because the user might have set a custom description
        if (!$pageSectionURL->getIsInitialized() || \in_array($pageSectionURL->getDescription(), [null, ''], true)) {
            $pageSectionURL->setDescription($pageMeta['description'] ?? null);
        }

        $pageSectionURL->setCoverImageUrl($pageMeta['og:image'] ?? null);

        if (null !== $faviconUrl = $pageMeta['icon'] ?? null) {
            // if the favicon URL is a relative path, we need to convert it to an absolute path
            if (\str_starts_with($faviconUrl, '/') && !\str_starts_with($faviconUrl, '//')) {
                $faviconUrl = \parse_url($url, PHP_URL_SCHEME) . '://' . \parse_url($url, PHP_URL_HOST) . $faviconUrl;
            }
        }

        $pageSectionURL->setFaviconUrl($faviconUrl);
    }
}