<?php

namespace App\Service\Integration;

use App\Entity\Prompt;
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
    public const CHAT_MODEL = 'gpt-4o-mini';

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
     * Requests a chat response from OpenAI based on the given context and prompt.
     * Writes the response and the spent tokens to the given prompt.
     */
    public function generatePromptChatResponse(Prompt &$prompt, array $messages, ?array $responseFormat = null): void
    {

        // // debug responses
        // $prompt->setResponseText('Hello World! This is a test response.');
        // $prompt->setPromptTokens(1);
        // $prompt->setCompletionTokens(1);
        // $prompt->setUpdatedAt(new \DateTime());

        // return;


        $createResponse = $this->getChatResponse($messages, $responseFormat);
        $prompt->setResponseText($createResponse->choices[0]->message->content);
        $prompt->setPromptTokens($createResponse->usage->promptTokens);
        $prompt->setCompletionTokens($createResponse->usage->completionTokens);
        $prompt->setUpdatedAt(new \DateTime());
    }

    /**
     * @return CreateResponse The chat response from OpenAI based on the given prompt
     */
    public function getChatResponse(array $messages = [], ?array $responseFormat = null): CreateResponse
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getAssistantSystemInstructions(),
            ],
            ...$messages,
        ];
        $chatOptions = [
            'model' => self::CHAT_MODEL,
            'messages' => $messages,
        ];

        if (null !== $responseFormat) {
            $chatOptions['response_format'] = $responseFormat;
        }

        $client = $this->getClient();
        $response = $client->chat()->create($chatOptions);

        return $response;
    }

    /**
     * This dictates how the assistant behaves - it provides exact instructions to the assistant.
     */
    public function getAssistantSystemInstructions(): string
    {
        return '
            1. Objective:
            Assist users with knowledge creation, analysis, and summarization.
            Ensure friendly, professional, and helpful interactions.

            2. Tone:
            Friendly and supportive.
            Maintain context-awareness across conversations but *do not* use sentences like "If you need help, let me know." or "If you have any questions, feel free to ask.".
            Always respond in the language the prompt has been written in. Try your best to translate the response into the same language as the prompt.
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