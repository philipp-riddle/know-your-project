<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Page\PageSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageSection>
 */
class PageSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageSection::class);
    }

    /**
     * @return PageSection[]
     */
    public function findByTask(Task $task): array
    {
        return $this->createQueryBuilder('ps')
            ->andWhere('ps.task = :task')
            ->setParameter('task', $task)
            ->orderBy('ps.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
