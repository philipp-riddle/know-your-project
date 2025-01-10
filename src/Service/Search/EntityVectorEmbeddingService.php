<?php

namespace App\Service\Search;

use App\Entity\Interface\UserPermissionInterface;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Repository\PageSectionRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\Integration\QdrantIntegration;
use Qdrant\Models\Filter\Filter;
use Ramsey\Uuid\Uuid;

/**
 * This service is the bridge between Qdrant (vector database) and the database + EntityVectorEmbeddingInterface classes.
 * It is responsible for searching and updating the vector embeddings.
 * 
 * When searching we have to make sure that the searching user has access to the found entities.
 * Centralizing this check here makes sure that no invalid data is returned to the user.
 */
final class EntityVectorEmbeddingService
{
    public function __construct(
        private QdrantIntegration $qdrant,

        // repositories to fetch the entities from the database based on the search results
        private PageSectionRepository $pageSectionRepository,
        private PageRepository $pageRepository,
        private TaskRepository $taskRepository,
        private ProjectRepository $projectRepository,
        private UserRepository $userRepository,
    ) { }

    /**
     * Searches for entities with the given embedded entity in the given project.
     * 
     * @param User $user The user who is searching. Required for permission checks.
     * @param EntityVectorEmbeddingInterface $entity The entity to search for.
     * @param string $search The search term to search for.
     * @param float $scoreTreshold The minimum score a search result needs to have to be considered relevant; otherwise it is skipped; defaults to 0.25.
     * @param Filter|null $filter The filter to apply to the search; if not provided the default filter of the embedded entity is used.
     * 
     * @return \Generator|array<array, UserPermissionInterface> The entities that match the vector embedding in the given project and the user has access to. Returned with a Generator to save memory.
     */
    public function searchEmbeddedEntity(User $user, EntityVectorEmbeddingInterface $entity, string $search, float $scoreTreshold, ?Filter $filter = null): \Generator
    {
        return $this->search($user, $search, $filter ?? $entity->getFilter(), $scoreTreshold);
    }

    /**
     * Searches for entities with the given search term.
     * 
     * @param User $user The user who is searching. Required for permission checks.
     * @param string $search The search term to search for.
     * @param Filter $filter The filter to apply to the search.
     * @param float $scoreTreshold The minimum score a search result needs to have to be considered relevant; otherwise it is skipped; defaults to 0.25
     * 
     * @return \Generator|array<array, UserPermissionInterface> The entities that match the vector embedding in the given project and the user has access to. Returned with a Generator to save memory.
     */
    public function search(User $user, string $search, Filter $filter, float $scoreTreshold): \Generator
    {
        return $this->getEntitySearchResults(
            $user,
            $this->qdrant->searchUserContent($search, $filter),
            $scoreTreshold
        );
    }

    /**
     * Searches for entities with the given vector embedding in the given project.
     * 
     * @param Project $project The project in which to search.
     * @param array $embedding The vector embedding to search for.
     * @param float $scoreTreshold The minimum score a search result needs to have to be considered relevant; otherwise it is skipped.
     * 
     * @return \Generator|array<array, UserPermissionInterface> The entities that match the vector embedding in the given project and the user has access to. Returned with a Generator to save memory.
     */
    public function searchWithVectorEmbedding(User $user, array $embedding, Filter $filter, float $scoreTreshold): \Generator
    {
        $searchResult = $this->qdrant->searchUserContentWithEmbedding($embedding, $filter);

        return $this->getEntitySearchResults($user, $searchResult, $scoreTreshold);
    }

    /**
     * Parses each search result from the vector database and converts it to a database entity.
     * 
     * @return \Generator|array<array, UserPermissionInterface> The search results as an array of arrays with the search result and the entity.
     */
    private function getEntitySearchResults(User $user, array $searchResult, float $scoreTreshold): \Generator
    {
        if ($searchResult['status'] !== 'ok') {
            throw new \RuntimeException('Qdrant search failed. Status: '.$searchResult['status']);
        }

        foreach ($searchResult['result'] as $result) {
            if ($result['score'] < $scoreTreshold) {
                continue;
            }

            $entity = $this->getDatabaseEntityFromVectorPayload($user, $result['payload']);

            if (null === $entity) {
                continue;
            }

            if ($entity instanceof EntityVectorEmbeddingInterface && \trim('') === \strip_tags($entity->getTextForEmbedding())) {
                continue; // skip entities that have no text to display
            }
            
            yield [$result, $entity]; // return an array consisting of the raw search result and the embedded entity   
        }
    }

    /**
     * Converts the payload of a search result from the vector database to an entity (into the UserPermission entity).
     * Here we can perfectly control what entities we want to display in the search results.
     * 
     * @param array $vectorPayload The payload of the search result from the vector database.
     * @return UserPermissionInterface|null The entity that corresponds to the search result; null if no entity was found with the given payload.
     */
    public function getDatabaseEntityFromVectorPayload(User $user, array $vectorPayload): ?UserPermissionInterface
    {
        // now check what ID is given -
        // the order is REALLY important here, because a page section is also a page, a task is also a page, ...
        if (null !== ($pageSectionId = $vectorPayload['pageSection'] ?? null)) {
            $entity = $this->pageSectionRepository->find($pageSectionId);
        } elseif (null !== $taskId = $vectorPayload['task'] ?? null) {
            $entity = $this->taskRepository->find($taskId);
        } elseif (null !== ($pageId = $vectorPayload['page'] ?? null)) {
            $entity = $this->pageRepository->find($pageId);
        } elseif (null !== $projectId = $vectorPayload['project'] ?? null) {
            $entity = $this->projectRepository->find($projectId);
        } elseif (null !== $userId = $vectorPayload['user'] ?? null) {
            $entity = $this->userRepository->find($userId);
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
            return null; // if the user does not have access to any entity just ignore it
        }

        return $entity;
    }

    public function updateEmbeddedEntity(EntityVectorEmbeddingInterface $entity): void
    {
        $metaAttributes = $entity->getMetaAttributes();
        $metaAttributes['type'] = (new \ReflectionClass($entity))->getShortName();

        $embedding = $this->qdrant->insertUserContent(
            $this->getEntityUuid($entity),
            $entity->getTextForEmbedding(),
            $metaAttributes,
        );

        // write the embedding to the embedded entity, e.g. to  work with it in-memory for the duration of this request
        $entity->processVectorEmbedding($embedding);
    }

    public function deleteEmbeddedEntity(EntityVectorEmbeddingInterface $entity, int $entityId): void
    {
        $this->qdrant->deleteUserContent($this->getEntityUuid($entity, $entityId));
    }

    /**
     * Returns a UUID for the given entity.
     * This UUID is unique for the entity and can be used for embedding into the vector database and deleting it.
     * 
     * @throws \InvalidArgumentException If the entity does not have an ID (probably because it is not persisted to the database yet).
     * @param EntityVectorEmbeddingInterface $entity The entity for which to generate the unique ID.
     * @param int|null $entityId The ID of the entity. If not provided, the ID of the entity is used.
     * @return string The unique ID.
     */
    private function getEntityUuid(EntityVectorEmbeddingInterface $entity, ?int $entityId = null): string
    {
        $entityId = $entityId ?? $entity->getId();

        if (null === $entityId) {
            throw new \InvalidArgumentException('Entity needs to have an ID to generate a unique embedding ID');
        }

        $entityShortName = (new \ReflectionClass($entity))->getShortName();
        $entityUniqueId = \sprintf('%s_%s', $entityShortName, $entityId);
        $entityUuid = Uuid::uuid3(Uuid::NAMESPACE_DNS, $entityUniqueId)->toString();

        return $entityUuid;
    }
}