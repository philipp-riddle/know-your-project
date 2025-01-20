<?php

namespace App\Repository;

use App\Entity\Project\ProjectUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectUser>
 *
 * @method ProjectUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectUser[]    findAll()
 * @method ProjectUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectUser::class);
    }
}
