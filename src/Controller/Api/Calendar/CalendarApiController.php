<?php

namespace App\Controller\Api\Calendar;

use App\Controller\Api\ApiController;
use App\Entity\Project\Project;
use App\Exception\BadRequestException;
use App\Repository\CalendarEventRepository;
use App\Repository\TaskRepository;
use App\Service\Helper\ApiControllerHelperService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/calendar')]
class CalendarApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private TaskRepository $taskRepository,
        private CalendarEventRepository $calendarEventRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/events/{project}/{from}/{to}', methods: ['GET'], name: 'api_calendar_events')]
    public function projectEvents(Project $project, string $from, string $to): JsonResponse
    {
        $this->checkUserAccess($project);

        if (false === \strtotime($from) || false === \strtotime($to)) {
            throw new BadRequestException('Invalid date format for "from" or "to" parameter');
        }

        return $this->createJsonResponse([
            'tasks' => $this->normalize($this->taskRepository->findProjectTasks($project, dueDateFrom: $from, dueDateTo: $to)),
            'events' => $this->normalize($this->calendarEventRepository->findProjectEvents($project, dateFrom: $from, dateTo: $to)),
        ]);
    }
}