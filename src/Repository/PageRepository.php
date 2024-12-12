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
    public function findProjectPages(User $user, Project $project): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('(p.project = :project AND p.user IS NULL) OR (p.project = :project AND p.user = :user)') // find either project pages or user pages for the project
            ->setParameter('project', $project)
            ->setParameter('user', $user)
            ->orderBy('p.user', 'DESC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
