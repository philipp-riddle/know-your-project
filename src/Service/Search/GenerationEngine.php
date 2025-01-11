<?php

namespace App\Service\Search;

use App\Entity\Page;
use App\Entity\PageSectionAIPrompt;
use App\Service\Integration\OpenAIIntegration;

/**
 * This class is responsible for creating new content, based on existing vector embeddings and user prompted input.
 */
final class GenerationEngine
{
    public function __construct(
        private OpenAIIntegration $openAIIntegration,
    ) { }

    public function generatePageSectionAIPrompt(Page $page, PageSectionAIPrompt $aiPrompt): PageSectionAIPrompt
    {
        if (\trim($aiPrompt->getPrompt()) === '') {
            $chatResponse = ''; // No prompt, no response
        } else {
            $chatCreateResponse = $this->openAIIntegration->getChatResponse($this->getPageContextHTML($page), $aiPrompt->getPrompt());
            $chatResponse = $chatCreateResponse->choices[0]->message->content;
        }

        $aiPrompt->setResponseText($chatResponse);

        return $aiPrompt;
    }

    private function getPageContextHTML(Page $page): string
    {
        $pageHtml = \sprintf('<h1>%s</h1>', $page->getName()); // The page name is the title of the page

        foreach ($page->getPageTabs()[0]?->getPageSections() ?? [] as $section) {
            $pageHtml .= $section->getTextForEmbedding();
        }

        return $pageHtml;
    }
}