<?php

namespace App\Service\Search;

use App\Entity\Page;
use App\Entity\PageSectionAIPrompt;
use App\Entity\PageSectionEmbeddedPage;
use App\Service\Integration\OpenAIIntegration;

/**
 * This class is responsible for creating new content, based on existing context data and user prompted input.
 */
final class GenerationEngine
{
    public const GENERAL_CONTEXT_INSTRUCTION = 'General context is about the page the user is currently on and is prioritized over other contexts.';
    public const EMBEDDED_PAGES_CONTEXT_INSTRUCTION = 'Embedded pages are the foundation of the page and are included in the context, in prioritization right behind the page context.';

    public function __construct(
        private OpenAIIntegration $openAIIntegration,
    ) { }

    public function generatePageSectionAIPrompt(Page $page, PageSectionAIPrompt $aiPrompt): PageSectionAIPrompt
    {
        if (\trim($aiPrompt->getPrompt()) === '') {
            $chatResponse = ''; // No prompt, no response
        } else {
            // we use multiple contexts in the chat response to clearly differentiate between the different contexts and take the most ouf the context we have.
            $context = [
                self::GENERAL_CONTEXT_INSTRUCTION => $this->getPageContextHTML($page),
                self::EMBEDDED_PAGES_CONTEXT_INSTRUCTION => $this->getEmbeddedPageContextHTML($page),
            ];
            $chatCreateResponse = $this->openAIIntegration->getChatResponse($context, $aiPrompt->getPrompt());
            $chatResponse = $chatCreateResponse->choices[0]->message->content;
        }

        $aiPrompt->setResponseText($chatResponse);

        return $aiPrompt;
    }

    private function getPageContextHTML(Page $page): string
    {
        $pageHtml = \sprintf('<h1>%s</h1>', $page->getName()); // The page name is the title of the page

        foreach ($page->getTags() as $tagPage) {
            $pageHtml .= \sprintf('<span>Has (Project) Tag %s</span>', $tagPage->getTag()->getName());
        }

        foreach ($page->getPageTabs()[0]?->getPageSections() ?? [] as $section) {
            if (!$section instanceof PageSectionEmbeddedPage) {
                $pageHtml .= '<section>';
                $pageHtml .= $section->getTextForEmbedding();
                $pageHtml .= '</section>';
            }

        }

        return $pageHtml;
    }

    public function getEmbeddedPageContextHTML(Page $page): string
    {
        $embeddedPageContextHTML = '';

        foreach ($page->getPageTabs()[0]?->getPageSections() ?? [] as $section) {
            if ($section instanceof PageSectionEmbeddedPage) {
                $embeddedPageContextHTML .= $this->getPageContextHTML($section->getEmbeddedPage()->getPage());
            }
        }

        return $embeddedPageContextHTML;
    }
}