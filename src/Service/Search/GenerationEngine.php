<?php

namespace App\Service\Search;

use App\Entity\Page;
use App\Entity\PageSectionAIPrompt;
use App\Entity\PageSectionEmbeddedPage;
use App\Entity\Thread;
use App\Entity\ThreadItemPrompt;
use App\Service\Integration\OpenAIIntegration;

/**
 * This class is responsible for creating new content, based on existing context data and user prompted input.
 */
final class GenerationEngine
{
    public const GENERAL_CONTEXT_INSTRUCTION = 'General context is about the page the user is currently on and is prioritized over other contexts. Here is the content of the page:';
    public const EMBEDDED_PAGES_CONTEXT_INSTRUCTION = 'Embedded pages are the foundation of the page and are included in the context, in prioritization right behind the page context. Here is the content of the embedded pages:';
    public const USER_PROMPT_INSTRUCTION = 'Now you receive the user prompt. Make sure to not hallucinate and make sure to provide a response that is relevant to the context. Here is the prompt:';
    public const THREAD_MESSAGES_CONTEXT = 'Thread messages are the context of the thread the user is currently in.';

    public function __construct(
        private OpenAIIntegration $openAIIntegration,
    ) { }

    public function generatePageSectionAIPrompt(Page $page, PageSectionAIPrompt $aiPrompt): void
    {
        $prompt = $aiPrompt->getPrompt();

        if (\trim($prompt->getPromptText()) === '') {
            return;
        }

        $messages = [
            // we use multiple contexts in the chat response to clearly differentiate between the different contexts and take the most ouf the context we have.
            ...$this->getPageContextMessages($page),
            [
                'role' => 'user',
                'content' => self::USER_PROMPT_INSTRUCTION.$prompt->getPromptText(),
            ],
        ];
        $this->openAIIntegration->generatePromptChatResponse($prompt, $messages);
    }

    public function generateThreadItemPrompt(ThreadItemPrompt $threadItemPrompt): void
    {
        $prompt = $threadItemPrompt->getPrompt();

        if (\trim($prompt->getPromptText()) === '') {
            return; 
        }

        if (null !== $pageSectionContext = $threadItemPrompt->getThreadItem()->getThread()->getPageSectionContext()) {
            $messages = $this->getPageContextMessages($pageSectionContext->getPageSection()->getPageTab()->getPage());
        } else {
            throw new \RuntimeException('The thread item must have a page section context to generate a chat response.');
        }

        // now attach all thread messages to the context
        foreach ($threadItemPrompt->getThreadItem()->getThread()->getThreadItems() as $threadItem) {
            if (null !== $threadItemPrompt = $threadItem->getItemPrompt()) {
                $prompt = $prompt;
                $messages[] = [
                    'role' => 'user',
                    'content' => self::THREAD_MESSAGES_CONTEXT.$prompt->getPromptText(),
                ];

                if (null !== $prompt->getResponseText() && '' !== \trim($prompt->getResponseText())) {
                    $messages[] = [
                        'role' => 'assistant',
                        'content' => $prompt->getResponseText(),
                    ];
                }
            }
        }

        $this->openAIIntegration->generatePromptChatResponse($prompt, $messages);
    }

    private function getPageContextMessages(Page $page): array
    {
        return [
            [
                'role' => 'user',
                'content' => self::GENERAL_CONTEXT_INSTRUCTION.$this->getPageContextHTML($page),
            ],
            [
                'role' => 'user',
                'content' => self::EMBEDDED_PAGES_CONTEXT_INSTRUCTION.$this->getEmbeddedPageContextHTML($page),
            ],
        ];
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