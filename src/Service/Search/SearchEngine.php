<?php

namespace App\Service\Search;

use App\Entity\Interface\EntityVectorEmbeddingInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Repository\PageSectionRepository;
use App\Service\Helper\DefaultNormalizer;

/**
 * This class is the bridge between our entities in the database and the embeddings in the vector database.
 * The vector database only returns a list of IDs and scores for a search query - this needs to be converted back to the original entities in a readable format.
 * 
 * It uses an algorithm to  further calculate the score we got from the vector database search. It additionally groups them by familiar entities, e.g. pages and their sections.
 */
final class SearchEngine
{
    public const SEARCH_SCORE_THRESHOLD = 0.25; // the minimum score a search result needs to have to be considered relevant; otherwise it is skipped

    public function __construct(
        private VectorEmbeddingService $vectorEmbeddingService,
        private PageSectionRepository $pageSectionRepository,
        private PageRepository $pageRepository,
        private DefaultNormalizer $normalizer,
    ) { }

    /**
     * @return array The entities that match the search term in the given project; each entity is represented as an array like ['type' => 'Page', 'data' => [...], ...]
     */
    public function search(User $user, Project $project, string $searchTerm): array
    {
        $rawSearchResults = $this->vectorEmbeddingService->search($project, $searchTerm);
        $searchResults = [];

        foreach ($rawSearchResults as $rawSearchResult) {
            if ($rawSearchResult['score'] < self::SEARCH_SCORE_THRESHOLD) {
                continue; // skip search results that are not relevant enough
            }

            if (null !== $entity = $this->getDatabaseEntity($user, $rawSearchResult['payload'])) {
                if ($entity instanceof EntityVectorEmbeddingInterface && \trim('') === $entity->getTextForEmbedding()) {
                    continue; // skip entities that have no text to display
                }

                $entityType = (new \ReflectionClass($entity))->getShortName();

                $searchResults[] = [
                    'id' => \sprintf('%s:%s', $entityType, $entity->getId()), // unique ID for the search result; this makes it easier in Vue to keep track of the results!
                    'type' => $entityType,
                    'score' => $this->calculateScore($entity, $searchTerm, $rawSearchResult),
                    'result' => $this->normalizer->normalize($entity),
                ];
            }
        }

        // group the search results by familiar entities;
        //e.g. pages and their sections into one result, sections are sub-results
        $searchResults = $this->groupSearchResults($searchResults);

        // sort the search results by score
        \usort($searchResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return $searchResults;
    }

    /**
     * Groups the search results by the page they belong to.
     * If only the page section matches but not the page itself, the section is not a sub result.
     * 
     * @param array $searchResults The search results to group.
     * @return array The grouped search results.
     */
    protected function groupSearchResults(array $searchResults): array
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
            } elseif ($entityType === 'Task') {
                $searchContainers['task'.$$entityData['id']][] = $searchResult;
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
    protected function calculateScore(UserPermissionInterface $entity, string $searchTerm, array $rawSearchResult): float
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

    /**
     * Converts the payload of a search result from the vector database to an entity (into the UserPermission entity).
     * Here we can perfectly control what entities we want to display in the search results.
     * 
     * @param array $payload The payload of the search result from the vector database.
     * @return UserPermissionInterface|null The entity that corresponds to the search result; null if no entity was found with the given payload.
     */
    protected function getDatabaseEntity(User $user, array $payload): ?UserPermissionInterface
    {
        if (null !== ($pageSectionId = $payload['pageSection'] ?? null)) {
            $entity = $this->pageSectionRepository->find($pageSectionId);
        } elseif (null !== ($pageId = $payload['page'] ?? null)) {
            $entity = $this->pageRepository->find($pageId);
        } else {
            return null;
        }

        if (null === $entity) {
            return null;
        }

        if (!($entity instanceof UserPermissionInterface)) {
            throw new \InvalidArgumentException('Entity does not implement UserPermissionInterface, cannot be used in search: ' . \get_class($entity));
        }

        if (!$entity->hasUserAccess($user)) {
            return null;
        }

        return $entity;
    }
}