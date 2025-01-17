<?php

namespace App\Service\Search;

use App\Entity\Page;
use App\Entity\PageSectionAIPrompt;
use App\Entity\PageSectionEmbeddedPage;
use App\Entity\PageSectionSummary;
use App\Entity\Prompt;
use App\Entity\Thread;
use App\Entity\ThreadItemPrompt;
use App\Entity\User;
use App\Service\Integration\OpenAIIntegration;

/**
 * This class is responsible for creating new content, based on existing context data and user prompted input.
 * It generates prompts for the AI to respond to, based on the context and what is required, e.g. summary requires different prompts than a general prompt.
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

    public function generatePageSummary(User $currentUser, Page $page, PageSectionSummary $summary): void
    {
        $pageHtml = \trim($page->getHtmlSummary());

        if (\trim($pageHtml) === '') {
            return;
        }

        $summarizeUserMessage = 'Here is the HTML content of the summary I want to summarize: '.$pageHtml;
        $messages = [
            [
                // inject an additional system instruction to the chat response to make sure the assistant knows what to do with this special task.
                'role' => 'system',
                'content' => '
                    You will be provided with a HTML of the page content.
                    Please summarize the page content and make sure that the summary is concise and highlights the most important information.
                    Please make sure to leave as many key terms as possible in the summary.
                ',
            ],
            [
                'role' => 'user',
                'content' => $summarizeUserMessage,
            ],
        ];
        $prompt = $summary->getPrompt();

        if (null === $prompt) {
            $prompt = (new Prompt())
                ->initialize()
                ->setUser($currentUser)
                ->setPromptText($summarizeUserMessage)
                ->setProject($page->getProject());
            $summary->setPrompt($prompt);
        }

        $this->openAIIntegration->generatePromptChatResponse($prompt, $messages);
    }

    public function generatePageSectionAIPrompt(Page $page, PageSectionAIPrompt $aiPrompt): void
    {
        $prompt = $aiPrompt->getPrompt();

        if (\trim($prompt->getPromptText()) === '') {
            return;
        }

        $messages = [
            [
                'role' => 'system',
                'content' => '
                    Answer in HTML format for easy integration into the web page and better readability. Start with <h3> tags.
                    If asked for creativity suggest next steps on what to add to the page or highlight incomplete  checklist items.
                ',
            ],
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
                'content' => self::GENERAL_CONTEXT_INSTRUCTION.$this->getHtmlSummary($page),
            ],
            [
                'role' => 'user',
                'content' => self::EMBEDDED_PAGES_CONTEXT_INSTRUCTION.$this->getEmbeddedPageContextHTML($page),
            ],
        ];
    }

    // @todo implement an extensive version of the page HTML to feed into the LLM; when summarizing etc we want minimal information to not distract the LLM
    public function getHtmlSummary(Page $page): string
    {
        $pageHtml = \sprintf('<h1>%s</h1>', $page->getName()); // The page name is the title of the page

        foreach ($page->getTags() as $tagPage) {
            $pageHtml .= \sprintf('<span>Has Tag %s</span>', $tagPage->getTag()->getName());
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
                $embeddedPageContextHTML .= $page->getHTML($section->getEmbeddedPage()->getPage());
            }
        }

        return $embeddedPageContextHTML;
    }
}