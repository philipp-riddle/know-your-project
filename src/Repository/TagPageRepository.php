<?php

namespace App\Repository;

use App\Entity\Tag\Tag;
use App\Entity\Tag\TagPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagPage>
 */
class TagPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagPage::class);
    }

    /**
     * @return TagPage[]
     */
    public function findAllTagPagesByTag(Tag $tag): array
    {
        return $this->createQueryBuilder('tp')
            ->andWhere('tp.tag = :tag')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult();
    }
}
