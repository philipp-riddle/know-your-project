<?php

namespace App\Service\Search;

use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionAIPrompt;
use App\Entity\Page\PageSectionEmbeddedPage;
use App\Entity\Page\PageSectionSummary;
use App\Entity\Project\Project;
use App\Entity\Prompt;
use App\Entity\Tag\Tag;
use App\Entity\Task;
use App\Entity\Thread\ThreadItemPrompt;
use App\Entity\User\User;
use App\Repository\TagRepository;
use App\Service\Helper\DefaultNormalizer;
use App\Service\Integration\OpenAIIntegration;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This class is responsible for creating new content or filtering existing content, based on the user prompt input.
 * It generates prompts for the AI to respond to, based on the context and what is required, e.g. summary requires different prompts than a general prompt.
 * 
 * Current generation possibilities:
 *      - Starter points for creating new pages, tasks, and recommending relevant content (=> returning title, content, and suggested tag)
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
        private DefaultNormalizer $defaultNormalizer,
        private TagRepository $tagRepository,
    ) { }

    public function generateCreationPrompt(User $user, Page $page, string $prompt): array
    {
        // get relevant context (pages, page sections) project; this improves the query results
        $searchResults = $this->entityVectorEmbeddingService->searchEmbeddedEntity($user, $page->getProject(), $prompt, self::ANSWER_RELEVANCE_SCORE_TRESHOLD);
        $aggregatedSearchResults = []; // after iterating over a search result add it to the item; otherwise we cannot retrieve it anymore (=> is a Generator and cannot be rewinded)
        $relevantContext = [];
        $recommendedTag = null;

        foreach ($searchResults as $searchResult) {
            list ($searchResult, $entity) = $searchResult;

            // if it's not an embedded entity or a page section, skip it
            // we only want top-level pages and tasks in the context; this prevents confusing the LLM with duplicate content
            if (!($entity instanceof EntityVectorEmbeddingInterface) || $entity instanceof PageSection) {
                continue;
            }

            if (null !== $textForEmbedding = $entity->getTextForEmbedding()) {
                $relevantContext[] = '=== CONTEXT === \n'.$textForEmbedding;

                // get the first tag of the entity to recommend it to the user
                if (null === $recommendedTag) {
                    if ($entity instanceof Page) {
                        $recommendedTag = @$entity->getTags()[0]?->getTag();
                    } elseif ($entity instanceof PageSection) {
                        $recommendedTag = @$entity->getPageTab()->getPage()->getTags()[0]?->getTag();
                    } elseif ($entity instanceof Task) {
                        $recommendedTag = @$entity->getPage()->getTags()[0]?->getTag();
                    }
                }
            }

            $aggregatedSearchResults[] = [$searchResult, $entity];
        }

        // helper variables which help us contextualize the response and make sure the AI knows what to do with the prompt.
        $typeMessageHint = $page->getTask() === null ? 'page' : 'task';

        // the messages which are sent to the AI.
        // these act as an assistant and guidelines to help the AI generate a response.
        $messages = [
            [
                'role' => 'system',
                'content' => \sprintf('
                    You are an assistant to help with creating a %s.
                    You will  first provided with context ("1. CONTEXT"; each context item delimited by "=== CONTEXT ===") and then the user question ("2. QUESTION").
                    If you have no provided context you can still provide an answer BUT IT IS VERY IMPORTANT TO STATE THIS IN THE RESPONSE!!

                    Respond in JSON format, adhering to the provided JSON schema.
                    Return a title, describing the nature of the creation, and content in HTML format for better readibility.
                    DO NOT start with a <h4> tag and DO NOT repeat the title in the content.
                ', $typeMessageHint,),
            ],
            [
                'role' => 'user',
                'content' => '1. CONTEXT: '.\implode("\n", $relevantContext),
            ],
            [
                'role' => 'user',
                'content' => '2. PROMPT: '.$prompt,
            ],
        ];
        // this format dictates in which format the AI should respond.
        // this makes it more reliable and we can extract the response in a structured way.
        $responseJsonFormat = $this->getTitleAndContentJsonSchema(\ucfirst($typeMessageHint));

        // if it is a task we must slightly adjust the JSON schema to include a checklist.
        if (null !== $page->getTask()) {
            $responseJsonFormat['json_schema']['schema']['properties']['checklist'] = [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                ],
                'description' => 'Checklist items to work on the content provided before.',
            ];
            $responseJsonFormat['json_schema']['schema']['required'][] = 'checklist';
        }

        $promptEntity = (new Prompt())
            ->initialize()
            ->setUser($user)
            ->setProject($page->getProject())
            ->setPromptText($prompt);

        // call OpenAI API to generate a response with the user context and user question.
        $this->openAIIntegration->generatePromptChatResponse($promptEntity, $messages, $responseJsonFormat);
        $answer = \json_decode($promptEntity->getResponseText(), true);
        // add the previously found recommended tag to the answer
        $answer['tag'] = $recommendedTag ? $this->defaultNormalizer->normalize($user, $recommendedTag) : null;

        return [
            // use the search engine to parse the search results into a format the frontend can easily parse and understand.
            'searchResults' => $this->searchEngine->parseSearchResults($user, $prompt, $aggregatedSearchResults),

            // this is the JSON answer from the assistant.
            // decode it to an array and pass it to the frontend to start.
            'answer' => $answer,
        ];
    }

    /** 
     * Generates an answer based on the user question and the context of the project.
     * 
     * @param User $user The user asking the question.
     * @param Project $project The project the user is currently working on.
     * @param string $question The question the user is asking.
     * 
     * @return array An array of search results and messages to be sent to the user.
     */
    public function generateAnswer(User $user, Project $project, string $question): array
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
                    If there is no relevant context, you can still provide an answer BUT IT IS VERY IMPORTANT TO STATE THIS IN THE RESPONSE.

                    Respond in JSON format, adhering to the provided JSON schema.
                    Return a title, describing the nature of the creation, and content in HTML format for better readibility.
                    DO NOT START WITH A HEADING; Start either with a paragraph or a list of items to make the content more readable. TRY NOT TO OVERSHARE.

                    The user will now start with the 1. CONTEXT and then 2. QUESTION.
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
            ->setPromptText($question);

        // call OpenAI API to generate a response with the user context and user question.
        // make sure to provide a response format to the AI to generate a structured response.
        $this->openAIIntegration->generatePromptChatResponse($prompt, $messages, responseFormat: $this->getTitleAndContentJsonSchema('Answer'));

        return [
            // use the search engine to parse the search results into a format the frontend can easily parse and understand.
            'searchResults' => $this->searchEngine->parseSearchResults($user, $question, $aggregatedSearchResults),

            // this is the JSON answer from the assistant; includes title and content.
            // decode it to an array and pass it to the frontend to display all returned schema properties.
            'answer' => \json_decode($prompt->getResponseText(), true),
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

    /**
     * This JSON schema can be used to dictate the AI to respond in a certain format.
     * This prevents the AI from generating responses that are not structured and hard to parse.
     * 
     * Having a title and a content is also perfect for saving the generated content in the next step to a page.
     * 
     * @param string $schemaName The name of the schema to use; only used for reference.
     * 
     * @return array The JSON schema for the title and content. 
     */
    private function getTitleAndContentJsonSchema(string $schemaName): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => $schemaName,
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Title for the content, precise maximum three words summary',
                        ],
                        'content' => [
                            'type' => 'string',
                            'description' => 'The content ',
                        ],
                    ],
                    'required' => ['title', 'content'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}