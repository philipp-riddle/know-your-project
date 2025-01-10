<?php

namespace App\Service\Search;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\User;
use App\Service\Helper\DefaultNormalizer;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use Qdrant\Models\Filter\Condition\MatchInt;

/**
 * This class is responsible for recommending content (pages, tasks, page sections) to users based on their input and current context.
 * (e.g. editing a page => recommend other pages which are famililar content-wise).
 */
final class RecommendationEngine
{
    public const RECOMMENDATION_ENGINE_SCORE_TRESHOLD = 0.25;

    public function __construct(
        private EntityVectorEmbeddingService $entityVectorEmbeddingService,
        private DefaultNormalizer $normalizer,
    ) { }

    /**
     * Recommends similar content to the user based on three given entities, explained below.
     * 
     * @param User $user The user for which to recommend content.
     * @param EntityVectorEmbeddingInterface $baseEntity The base entity to use as context for the recommendation; e.g. a newly created page or page section. It may hold the vector embedding in memory.
     * @param EntityVectorEmbeddingInterface $queryEntity The entity to use as query for the recommendation; e.g. a project (=> project-wide recommendations)
     * @param EntityVectorEmbeddingInterface|null $excludeEntity The entity to exclude from the recommendation or null. If null is passed the base entity is excluded. Example for this exclude value could be a page when the user is currently editing a page section.
     * 
     * @return array An array of recommended content.
     */
    public function recommendSimilarContent(User $user, EntityVectorEmbeddingInterface $baseEntity, EntityVectorEmbeddingInterface $queryEntity, ?EntityVectorEmbeddingInterface $excludeEntity = null): array
    {
        // first off we must exclude the entity itself from the search results we want to recommend
        $excludeEntity ??= $baseEntity;
        $excludeEntityShortName = \strtolower((new \ReflectionClass($excludeEntity))->getShortName());
        $filter = $queryEntity->getFilter();
        $filter->addMustNot(new MatchInt($excludeEntityShortName, $excludeEntity->getId()));

        if ($baseEntity instanceof CachedEntityVectorEmbedding && null !== $cachedEmbedding = $baseEntity->getCachedEmbedding()) {
            $searchResults = $this->entityVectorEmbeddingService->searchWithVectorEmbedding(
                $user,
                $cachedEmbedding,
                $filter,
                self::RECOMMENDATION_ENGINE_SCORE_TRESHOLD,
            );
        } else {
            $searchResults = $this->entityVectorEmbeddingService->searchEmbeddedEntity(
                $user,
                $queryEntity,
                $queryEntity->getTextForEmbedding(),
                self::RECOMMENDATION_ENGINE_SCORE_TRESHOLD,
                $filter,
            );
        }

        $parsedSearchResults = [];

        foreach ($searchResults as $searchResultResponse) {
            /**
             * @var array $searchResult The raw search result from the vector database.
             * @var UserPermissionInterface $entity The entity that was found in the database.
             */
            list ($searchResult, $entity) = $searchResultResponse;

            if (!($entity instanceof EntityVectorEmbeddingInterface)) {
                throw new \RuntimeException('The entity returned from the search results must implement the EntityVectorEmbeddingInterface. Class: '.\get_class($entity));
            }

            $flattenedSearchResult = $this->flattenSearchResult($searchResult, $entity);

            if (null === $flattenedSearchResult) {
                continue; // skip this search result if null was returned; this means it should be skipped
            }

            list ($id, $searchResult) = $flattenedSearchResult;
            $parsedSearchResults[$id] = $searchResult;
        }

        // sort the search results by score
        \usort($parsedSearchResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return \array_values($parsedSearchResults);
    }

    /**
     * Flattens the search results to the page entities.
     * In contrast to the search engine, the recommendation engine only shows one entity (e.g. pages). If additional page section matches are found the score will be increased.
     */
    private function flattenSearchResult(array $searchResult, EntityVectorEmbeddingInterface $entity): ?array
    {
        if ($entity instanceof PageSection) {
            // @todo use this - for now only pages
            // $page = $entity->getPageTab()->getPage(); // remap the page section to the page as we only want to show pages

            return null;
        } else if (!($entity instanceof Page)) {
            return null; // skip this search result if it is not a page or page section
        }

        $entityShortName = \strtolower((new \ReflectionClass($entity))->getShortName());
        $id = \sprintf('%s:%s', $entityShortName, $entity->getId());
        $searchResult = [
            'id' => $id,
            'type' => $entityShortName,
            'score' => $searchResult['score'], // @todo work with the count of page sections that match
            'result' => $this->normalizer->normalize($entity),
        ];

        return [$id, $searchResult];
    }
}