<?php

namespace App\Controller\Api\Calendar;

use App\Controller\Api\CrudApiController;
use App\Entity\Calendar\CalendarEvent;
use App\Entity\Project\Project;
use App\Entity\Tag\TagCalendarEvent;
use App\Exception\BadRequestException;
use App\Form\Calendar\CalendarEventForm;
use App\Repository\TagRepository;
use App\Service\Helper\ApiControllerHelperService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/calendar/event')]
class CalendarEventApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private TagRepository $tagRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', methods: ['POST'], name: 'api_calendar_event_create')]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (CalendarEvent $calendarEvent, FormInterface $form) {
                // assign the new event to the current user (in addition to assigning it to the project).
                $calendarEvent->setUser($this->getUser());

                /**
                 * Convert the numeric tag ID list to a Tag entity and eventually to TagCalendarEvent entities.
                 * @var int[]
                 * */
                $tagIds = $form->get('tags')->getData();

                foreach ($tagIds as $tagId) {
                    $tag = $this->tagRepository->find($tagId);

                    if (null === $tag) {
                        throw new BadRequestException('Invalid tag ID: '.$tagId);
                    } else {
                        $this->checkUserAccess($tag); // check if the user has access to the tag
                    }

                    $tagCalendarEvent = (new TagCalendarEvent())
                        ->setTag($tag)
                        ->setCalendarEvent($calendarEvent);
                    $calendarEvent->addEventTag($tagCalendarEvent);
                    $this->em->persist($tagCalendarEvent);
                }

                return $calendarEvent;
            },
        );
    }

    #[Route('/{calendarEvent}', methods: ['PUT'], name: 'api_calendar_event_update')]
    public function update(CalendarEvent $calendarEvent, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            $calendarEvent,
            $request,
        );
    }

    #[Route('/{calendarEvent}', methods: ['DELETE'], name: 'api_calendar_event_delete')]
    public function delete(CalendarEvent $calendarEvent): JsonResponse
    {
        return $this->crudDelete($calendarEvent);
    }

    #[Route('/list/{project}', methods: ['GET'], name: 'api_calendar_event_list')]
    public function list(Project $project): JsonResponse
    {
        $this->checkUserAccess($project);

        return $this->crudList(
            filters: [
                'project' => $project,
            ],
            orderBy: ['updatedAt' => 'DESC'], // always return the most recent events first
        );
    }

    public function getEntityClass(): string
    {
        return CalendarEvent::class;
    }

    public function getFormClass(): string
    {
        return CalendarEventForm::class;
    }
}