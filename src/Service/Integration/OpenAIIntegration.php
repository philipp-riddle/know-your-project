<?php

namespace App\Service\Integration;

use OpenAI;
use OpenAI\Client;

/**
 * This class is responsible for all operations with OpenAI.
 * We use OpenAI to generate text based on a prompt and to embed text into vectors we can then store to our vector database, i.e. Qdrant.
 */
class OpenAIIntegration
{
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
            'model' => 'text-embedding-3-small',
            'input' => $input,
        ]);

        return \array_values($response->embeddings[0]->embedding);
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