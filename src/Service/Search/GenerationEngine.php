<?php

namespace App\Service\Search;

use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionAIPrompt;
use App\Entity\Page\PageSectionEmbeddedPage;
use App\Entity\Page\PageSectionSummary;
use App\Entity\Project\Project;
use App\Entity\Prompt;
use App\Entity\ThreadItemPrompt;
use App\Entity\User\User;
use App\Service\Integration\OpenAIIntegration;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use Qdrant\Models\Filter\Condition\Range;

/**
 * This class is responsible for creating new content or filtering existing content, based on the user prompt input.
 * It generates prompts for the AI to respond to, based on the context and what is required, e.g. summary requires different prompts than a general prompt.
 * 
 * Current generation possibilities:
 *      - Answer questions (ask a question and answer using relevant context from the project)
 *      - Page summary (summarize the content of a page)
 *      - Page section AI prompt (answer a specific prompt in the context of a page section)
 *      - Thread item AI prompt (refine a specific prompt in the context of a thread item)
 */
final class GenerationEngine
{
    public const GENERAL_CONTEXT_INSTRUCTION = 'General context is about the page the user is currently on and is prioritized over other contexts. Here is the content of the page:';
    public const EMBEDDED_PAGES_CONTEXT_INSTRUCTION = 'Embedded pages are the foundation of the page and are included in the context, in prioritization right behind the page context. Here is the content of the embedded pages:';
    public const USER_PROMPT_INSTRUCTION = 'Now you receive the user prompt. Make sure to not hallucinate and make sure to provide a response that is relevant to the context. Here is the prompt:';
    public const THREAD_MESSAGES_CONTEXT = 'Thread messages are the context of the thread the user is currently in.';

    public const ANSWER_RELEVANCE_SCORE_TRESHOLD = 0.3;

    public function __construct(
        private OpenAIIntegration $openAIIntegration,
        private EntityVectorEmbeddingService $entityVectorEmbeddingService,
        private SearchEngine $searchEngine,
    ) { }

    /** 
     * Answer a question based on the user input and the context of the project.
     * 
     * @param User $user The user asking the question.
     * @param Project $project The project the user is currently working on.
     * @param string $question The question the user is asking.
     * 
     * @return array An array of search results and messages to be sent to the user.
     */
    public function answerQuestion(User $user, Project $project, string $question): array
    {
        // get relevant context (pages, page sections) project; this improves the query results
        $searchResults = $this->entityVectorEmbeddingService->searchEmbeddedEntity($user, $project, $question, self::ANSWER_RELEVANCE_SCORE_TRESHOLD);
        $aggregatedSearchResults = []; // after iterating over a search result add it to the item; otherwise we cannot retrieve it anymore (=> is a Generator and cannot be rewinded)
        $relevantContext = [];

        foreach ($searchResults as $searchResult) {
            list ($searchResult, $entity) = $searchResult;

            // if it's not an embedded entity or a page section, skip it
            // we only want top-level pages and tasks in the context; this prevents confusing the LLM with duplicate content
            if (!($entity instanceof EntityVectorEmbeddingInterface) || $entity instanceof PageSection) {
                continue;
            }

            if (null !== $textForEmbedding = $entity->getTextForEmbedding()) {
                $relevantContext[] = '=== CONTEXT === \n'.$textForEmbedding;
            }

            $aggregatedSearchResults[] = [$searchResult, $entity];
        }

        $messages = [
            [
                'role' => 'system',
                'content' => '
                    You will  first provided with context ("1. CONTEXT"; each context item delimited by "=== CONTEXT ===") and then the user question ("2. QUESTION").
                    Make sure to provide a relevant answer to the question and make use of the context provided.
                    Respond in HTML format for better readibility and start with a heading <h3> tag.
                    The heading should concisely describe the nature of the user question.
                ',
            ],
            [
                'role' => 'user',
                'content' => '1. CONTEXT: '.\implode("\n", $relevantContext),
            ],
            [
                'role' => 'user',
                'content' => '2. QUESTION: '.$question,
            ],
        ];

        $prompt = (new Prompt())
            ->initialize()
            ->setUser($user)
            ->setProject($project)
            ->setPromptText($question)
            ->setProject($project);

        // call OpenAI API to generate a response with the user context and user question.
        $this->openAIIntegration->generatePromptChatResponse($prompt, $messages);

        return [
            // use the search engine to parse the search results into a format the frontend can easily parse and understand.
            'searchResults' => $this->searchEngine->parseSearchResults($user, $question, $aggregatedSearchResults),

            // this is the answer from the assistant.
            'answer' => $prompt->getResponseText(),
        ];
    }

    public function generatePageSummary(User $currentUser, Page $page, PageSectionSummary $summary): void
    {
        $pageHtml = \trim($page->getTextForEmbedding());

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
                'content' => self::GENERAL_CONTEXT_INSTRUCTION.$page->getTextForEmbedding(),
            ],
            [
                'role' => 'user',
                'content' => self::EMBEDDED_PAGES_CONTEXT_INSTRUCTION.$this->getEmbeddedPageContextHTML($page),
            ],
        ];
    }

    public function getEmbeddedPageContextHTML(Page $page): string
    {
        $embeddedPageContextHTML = '';

        foreach ($page->getPageTabs()[0]?->getPageSections() ?? [] as $section) {
            if ($section instanceof PageSectionEmbeddedPage) {
                $embeddedPageContextHTML .= $page->getTextForEmbedding();
            }
        }

        return $embeddedPageContextHTML;
    }
}