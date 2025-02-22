<?php

namespace App\Repository;

use App\Entity\Project\Project;
use App\Entity\Tag\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @return Tag[]
     */
    public function findRootTags(Project $project): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.project = :project')
            ->andWhere('t.parent IS NULL')
            ->setParameter('project', $project)
            ->orderBy('t.orderIndex', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTagsByParent(Tag $tag): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.parent = :parent')
            ->setParameter('parent', $tag)
            ->orderBy('t.orderIndex', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
