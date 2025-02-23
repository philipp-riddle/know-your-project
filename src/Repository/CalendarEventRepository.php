<?php

namespace App\Repository;

use App\Entity\Calendar\CalendarEvent;
use App\Entity\Project\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendarEvent>
 */
class CalendarEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarEvent::class);
    }

    /**
     * @return CalendarEvent[]
     */
    public function findProjectEvents(Project $project, ?array $tags = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere('event.project = :project')
            ->setParameter('project', $project);

        // if the tags are null we assume the user wants all events
        // if the tags are empty we assume the user wants events without tags
        // if the tags are not empty we assume the user wants events with the given tags
        if (null !== $tags) {
            if (\count($tags) === 0) {
                $qb
                ->andWhere('event.eventTags IS EMPTY');
            } else {
                $qb
                    ->join('event.eventTags IS', 'eventTag')
                    ->join('eventTag.tag', 'tag')
                    ->andWhere('tag IN (:tags)')
                    ->setParameter('tags', $tags);
            }
        }

        if (null !== $dateFrom) {
            $qb
                ->andWhere('event.startDate >= :dateFrom OR event.endDate >= :dateFrom')
                ->setParameter('dateFrom', \date('Y-m-d 00:00:00', \strtotime($dateFrom)));
        }

        if (null !== $dateTo) {
            $qb
                ->andWhere('event.startDate <= :dateTo OR event.endDate <= :dateTo')
                ->setParameter('dateTo', \date('Y-m-d 23:59:59', \strtotime($dateTo)));
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
