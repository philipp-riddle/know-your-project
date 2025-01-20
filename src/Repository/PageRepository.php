<?php

namespace App\Repository;

use App\Entity\Page\Page;
use App\Entity\Project\Project;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * @return Page[]
     */
    public function findProjectPages(User $user, Project $project, bool $includeUserPages = true, ?string $query = null, ?int $limit = null, ?int $excludeId = null, ?array $tags = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.user', 'DESC')
            ->addOrderBy('p.name', 'ASC');

        if ($includeUserPages) {
            $qb
                ->andWhere('(p.project = :project AND p.user IS NULL) OR (p.project = :project AND p.user = :user)') // find either project pages or user pages for the project
                ->setParameter('project', $project)
                ->setParameter('user', $user);
        } else {
            $qb
                ->andWhere('p.project = :project AND p.user IS NULL') // find only project pages, no user pages
                ->setParameter('project', $project);
        }

        if (null !== $query) {
            $qb
                ->andWhere('p.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }
        
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $excludeId) {
            $qb
                ->andWhere('p.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        // if the tags are null we assume the user wants all pages
        // if the tags are empty we assume the user wants pages without tags
        // if the tags are not empty we assume the user wants pages with the given tags
        if (null !== $tags) {
            if (\count($tags) === 0) {
                $qb
                ->andWhere('p.tags IS EMPTY');
            } else {
                $qb
                    ->join('p.tags', 'tagPage')
                    ->join('tagPage.tag', 'tag')
                    ->andWhere('tag IN (:tags)')
                    ->setParameter('tags', $tags);
            }
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
