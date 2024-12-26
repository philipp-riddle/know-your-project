<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\Project;
use App\Entity\User;
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
    public function findProjectPages(User $user, Project $project, bool $includeUserPages = true, ?string $query = null, ?int $limit = null): array
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

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
