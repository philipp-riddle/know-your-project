<?php

namespace App\Repository;

use App\Entity\Project\Project;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findProjectTasks(Project $project, ?array $tags = null, ?string $dueDateFrom = null, ?string $dueDateTo = null): array
    {
        $qb = $this
            ->createQueryBuilder('task')
            ->andWhere('task.project = :project')
            ->setParameter('project', $project);

        // if the tags are null we assume the user wants all tasks
        // if the tags are empty we assume the user wants tasks without tags
        // if the tags are not empty we assume the user wants tasks with the given tags
        if (null !== $tags) {
            $qb->innerJoin('task.page', 'page');

            if (\count($tags) === 0) {
                $qb
                ->andWhere('page.tags IS EMPTY');
            } else {
                $qb
                    ->join('page.tags', 'tagPage')
                    ->join('tagPage.tag', 'tag')
                    ->andWhere('tag IN (:tags)')
                    ->setParameter('tags', $tags);
            }
        }

        if (null !== $dueDateFrom) {
            $qb
                ->andWhere('task.dueDate >= :dueDateFrom')
                ->setParameter('dueDateFrom', \date('Y-m-d 00:00:00', \strtotime($dueDateFrom)));
        }

        if (null !== $dueDateTo) {
            $qb
                ->andWhere('task.dueDate <= :dueDateTo')
                ->setParameter('dueDateTo', \date('Y-m-d 23:59:59', \strtotime($dueDateTo)));
        }

        return $qb
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Task[] Returns an array of Task objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
