<?php

namespace App\Service\Search;

use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Project;
use App\Entity\User;
use App\Service\Helper\DefaultNormalizer;

/**
 * This class is the bridge between our entities in the database and the embeddings in the vector database.
 * The vector database only returns a list of IDs and scores for a search query - this needs to be converted back to the original entities in a readable format.
 * 
 * It uses an algorithm to  further calculate the score we got from the vector database search. It additionally groups them by familiar entities, e.g. pages and their sections.
 */
final class SearchEngine
{
    /**
     * The minimum score a search result needs to have to be considered relevant; otherwise it is skipped.
     * 
     * @var float
     */
    public const SEARCH_SCORE_THRESHOLD = 0.2; // the minimum score a search result needs to have to be considered relevant; otherwise it is skipped

    public function __construct(
        private EntityVectorEmbeddingService $entityVectorEmbeddingService,
        private DefaultNormalizer $normalizer,
    ) { }

    /**
     * Searches for any content in the given project with the given search term.
     * 
     * @param User $user The user who is searching. Required for permission checks.
     * @param Project $project The project in which to search.
     * @param string $searchTerm The search term to search for.
     * 
     * @return UserPermissionInterface[] The entities that match the search term in the given project; each entity is represented as an array like ['type' => 'Page', 'data' => [...], ...]
     */
    public function searchProject(User $user, Project $project, string $searchTerm): array
    {
        return $this->parseSearchResults(
            $user,
            $searchTerm,
            $this->entityVectorEmbeddingService->searchEmbeddedEntity($user, $project, $searchTerm, self::SEARCH_SCORE_THRESHOLD),
        );
    }

    /**
     * Parses the raw search results from the vector database into a readable format and calculates the score for each search result.
     * 
     * @param User $user The user who is searching. Required for permission checks.
     * @param string $searchTerm The search term to search for.
     * @param \Generator|array<array, UserPermissionInterface> $searchResults The  search results from the vector embedding service.
     */
    public function parseSearchResults(User $currentUser, string $searchTerm, \Generator|array $searchResults): array
    {
        $parsedSearchResults = [];

        foreach ($searchResults as $searchResultResponse) {
            /**
             * @var array $searchResult The raw search result from the vector database.
             * @var UserPermissionInterface $entity The entity that was found in the database.
             */
            list ($searchResult, $entity) = $searchResultResponse;

            if ($entity instanceof EntityVectorEmbeddingInterface && \trim('') === $entity->getTextForEmbedding()) {
                continue; // skip entities that have no text to display
            }

            $entityType = (new \ReflectionClass($entity))->getShortName();

            $parsedSearchResults[] = [
                'id' => \sprintf('%s:%s', $entityType, $entity->getId()), // unique ID for the search result; this makes it easier in Vue to keep track of the results!
                'type' => $entityType,
                'score' => $this->calculateScore($entity, $searchTerm, $searchResult),
                'result' => $this->normalizer->normalize($currentUser, $entity),
            ];
        }

        // group the search results by familiar entities;
        //e.g. pages and their sections into one result, sections are sub-results
        $parsedSearchResults = $this->groupSearchResults($parsedSearchResults);

        // sort the search results by score
        \usort($parsedSearchResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return $parsedSearchResults;
    }

    /**
     * Groups the search results by the page they belong to.
     * If only the page section matches but not the page itself, the section is not a sub result.
     * 
     * @param array $searchResults The search results to group.
     * @return array The grouped search results.
     */
    private function groupSearchResults(array $searchResults): array
    {
        $searchContainers = [];

        foreach ($searchResults as $searchResult) {
            $entityType = $searchResult['type'];
            $entityData = $searchResult['result'];

            if ($entityType === 'Page') {
                $searchContainers['page_'.$entityData['id']] = [
                    ...$searchContainers['page_'.$entityData['id']] ?? [],
                    ...$searchResult,
                ];
            } elseif ($entityType === 'PageSection') {
                // sections are nested in the navigation; add them as sub-results to the page
                $pageId = $entityData['pageTab']['id'];
                $searchContainers['page_'.$pageId]['subResults'][] = $searchResult;
            } else {
                $searchContainers[\strtolower($entityType).'_'.$entityData['id']] = $searchResult;
            }
        }

        // now we turn the containers back into the search results format
        $searchResults = [];

        foreach ($searchContainers as $searchContainer) {
            // if the associated page is not a part of the search result, the page sections are a top-level result
            if (\array_keys($searchContainer) === ['subResults']) {
                foreach ($searchContainer['subResults'] as $section) {
                    $searchResults[] = $section;
                }
            } else {
                $searchResults[] = $searchContainer;
            }
        }

        return $searchResults;
    }

    /**
     * Although we get back a score from the vector database, this score can be improved.
     * We want to award higher scores to entities that are more relevant to the user - we do this by increasing scores for entities with matching content, etc.
     */
    private function calculateScore(UserPermissionInterface $entity, string $searchTerm, array $rawSearchResult): float
    {
        $score = $rawSearchResult['score'];

        if ($entity instanceof EntityVectorEmbeddingInterface) {
            $entityTextEmbedding = $entity->getTextForEmbedding();

            if (\str_contains(\strtolower($entityTextEmbedding), \strtolower($searchTerm))) {
                $score = $score * 2; // award a higher score if the search term is found in the text, independent of the case (lower/upper)
            }
        }

        $score = \min(1.0, $score); // make sure the score is not higher than 1.0

        return $score;
    }
}