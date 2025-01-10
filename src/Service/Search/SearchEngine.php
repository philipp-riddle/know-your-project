<?php

namespace App\Service\Search;

use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Repository\PageSectionRepository;
use App\Service\Helper\DefaultNormalizer;

/**
 * This class is the bridge between our entities in the database and the embeddings in the vector database.
 * The vector database only returns a list of IDs and scores for a search query - this needs to be converted back to the original entities in a readable format.
 */
final class SearchEngine
{
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
            if (null !== $entity = $this->getDatabaseEntity($user, $rawSearchResult['payload'])) {
                $entityType = (new \ReflectionClass($entity))->getShortName();

                $searchResults[] = [
                    'id' => \sprintf('%s:%s', $entityType, $entity->getId()), // unique ID for the search result; this makes it easier in Vue to keep track of the results!
                    'type' => $entityType,
                    'score' => $rawSearchResult['score'],
                    'result' => $this->normalizer->normalize($entity),
                ];
            }
        }

        // some custom logic for the search results: group the results by the page; i.e. nest the sections in the page
        // @todo

        return $searchResults;
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