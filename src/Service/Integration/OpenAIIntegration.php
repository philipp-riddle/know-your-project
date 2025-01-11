<?php

namespace App\Service\Integration;

use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;

/**
 * This class is responsible for all operations with OpenAI.
 * We use OpenAI to generate text based on a prompt and to embed text into vectors we can then store to our vector database, i.e. Qdrant.
 */
final class OpenAIIntegration
{
    public const TEXT_EMBEDDING_MODEL = 'text-embedding-3-small';
    public const CHAT_MODEL = 'gpt-3.5-turbo';

    /**
     * This function generates an array of embeddings for the given input text.
     * 
     * @param string $input The text to generate embeddings for
     * @return array The embeddings for the input text; this is an array of vertices/float numbers
     */
    public function createEmbedding(string $input): array
    {
        $client = $this->getClient();

        $response = $client->embeddings()->create([
            'model' => self::TEXT_EMBEDDING_MODEL,
            'input' => $input,
        ]);

        return \array_values($response->embeddings[0]->embedding);
    }

    /**
     * @return CreateResponse The chat response from OpenAI based on the given prompt
     */
    public function getChatResponse(string $context, string $prompt): CreateResponse
    {
        $client = $this->getClient();
        $response = $client->chat()->create([
            'model' => self::CHAT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getAssistantSystemInstructions(),
                ],
                [
                    'role' => 'user',
                    'content' => $this->getContextInstructions($context),
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        return $response;
    }

    public function getContextInstructions(string $context): string
    {
        return 'User Context is provided to enrich the chat session and to enhance the results. I will now provide you with context you must use and prioritize when answering questions. Here is the important context: '.$context;
    }

    /**
     * This dictates how the assistant behaves - it provides exact instructions to the assistant.
     */
    public function getAssistantSystemInstructions(): string
    {
        return '
            1. Objective:

            Assist users with knowledge creation, analysis, and summarization.
            Ensure friendly, professional, very consise, and helpful interactions.

            2. Tone:

            Friendly and supportive.
            Adapt tone based on user input (e.g., casual vs. formal).

            3. Behaviour:

            Maintain context-awareness across conversations but *do not* use sentences like "If you need help, let me know." or "If you have any questions, feel free to ask.".
            If asked suggest next steps or tools to complete tasks.
            Answer in HTML format for easy integration into web pages and better readability. Please start with <h3> tags.

            4. Features:

            4.1 Knowledge Creation: Guide brainstorming, suggest methods (e.g., Kanban, Double Diamond), and identify gaps.
            4.2 Analysis: Process data, find trends, and provide actionable insights.
            4.3 Summarization: Create concise overviews, abstracts, or reports.
            4.4 Dynamic Responses: Adjust length, format (e.g., bullet points, tables, or prose).
            4.5 Collaboration: Suggest ideas and refine outputs with user feedback.

            5. Examples:

            5.2 Brainstorming: "I need ideas for a product launch." Response: "Here are strategies: [list]. Want to prioritize or detail any?"
            5.3 Data Analysis: "Analyze this sales data." Response: "Key trends: [trends]. Want visualizations or next steps?"
            5.4Summarization: "Summarize this report." Response: "Summary: [points]. Expand on any section?"
        ';
    }

    /**
     * @return int The ID of the created vector store
     */
    public function createVectorStore(string $storeName): int
    {
        $client = $this->getClient();

        $response = $client->vectorStores()->create([
            'name' => $storeName,
        ]);

        return $response->id;
    }

    public function uploadFileToVectorStore(string $storeName, string $html)
    {

    }

    public function getClient(): Client
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';

        if (\trim($apiKey) === '') {
            throw new \RuntimeException('OPENAI_API_KEY is not set');
        }

        return OpenAI::client($apiKey);
    }
}