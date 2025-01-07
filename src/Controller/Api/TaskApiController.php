<?php

namespace App\Controller\Api;

use App\Entity\PageSection;
use App\Entity\Task;
use App\Form\MoveTaskForm;
use App\Form\TaskForm;
use App\Repository\TaskRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\OrderListHandler;
use App\Service\PageService;
use App\Service\TaskService;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/task')]
class TaskApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private TaskService $taskService,
        private TaskRepository $taskRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/{task}', name: 'api_task_get', methods: ['GET'])]
    public function getTask(Task $task): JsonResponse
    {
        return $this->crudGet($task);
    }

    #[Route('/list/{workflowStepType}', name: 'api_task_list', methods: ['GET'])]
    public function getTasks(string $workflowStepType): JsonResponse
    {
        return $this->jsonSerialize(
            $this->taskService->getTasks($this->getUser()->getSelectedProject(), $workflowStepType),
            normalizeCallbacks: [
                'pageTabs' => fn (Collection $pageTabs) => [$pageTabs[0] ?? []],
                'pageSections' => function (Collection $pageSections) {
                    // for the task serialisation we are only interested in the checklists to show the progress in the task list;
                    // this decreases the payload size heavily.
                    return \array_filter(\iterator_to_array($pageSections), fn(PageSection $pageSection) => $pageSection->getPageSectionChecklist() !== null);
                },
            ],
        );
    }

    #[Route('/{workflowStepType}', name: 'api_task_create', methods: ['POST'])]
    public function createTask(string $workflowStepType, Request $request, OrderListHandler $orderListHandler, PageService $pageService): JsonResponse
    {
        return $this->crudUpdateOrCreateOrderListItem(
            null,
            $request,
            $orderListHandler,
            itemsToOrder: function (Task $task) use ($workflowStepType) {
                return $this->taskService->getTasks($task->getProject(), $workflowStepType);
            },
            onProcessEntity: function (Task $task) use ($workflowStepType, $pageService) {
                $task
                    ->setProject($this->getUser()->getSelectedProject())
                    ->setStepType($workflowStepType)
                    ->setPage($pageService->createDefaultPage($task));

                return $task;
            },
        );
    }

    #[Route('/{task}', name: 'api_task_update', methods: ['PUT'])]
    public function updateTask(Task $task, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate($task, $request);
    }

    #[Route('/{task}', name: 'api_task_delete', methods: ['DELETE'])]
    public function deleteTask(Task $task): JsonResponse
    {
        return $this->crudDelete($task);
    }

    #[Route('/{workflowStepType}/order', name: 'api_task_order', methods: ['POST'])]
    public function changeTaskOrder(string $workflowStepType, Request $request, OrderListHandler $orderListHandler)
    {
        // these are all the tasks we want to change the order for
        $tasks = $this->taskService->getTasks(
            $this->getUser()->getSelectedProject(),
            $workflowStepType,
        );

        return $this->crudChangeOrder($request, $orderListHandler, $tasks);
    }

    #[Route('/{task}/move', name: 'api_task_move', methods: ['POST'])]
    public function moveTask(Task $task, Request $request)
    {
        return $this->crudUpdateOrCreate(
            $task,
            $request,
            formClass: MoveTaskForm::class,
            onProcessEntity: function (Task $newTask) use ($task) {
                $newStepType = $newTask->getStepType();

                if ($task->getStepType() === $newStepType) {
                    return $this->json([
                        'error' => \sprintf(
                            'Already part of step "%s"; to change order use the /order endpoint',
                            $task->getStepType(),
                        ),
                    ], 400);
                }

                $task->setOrderIndex($newTask->getOrderIndex());
                $this->taskService->addTaskToWorkflowStep($task, $newStepType);

                return $task;
            },
        );
    }

    public function getEntityClass(): string
    {
        return Task::class;
    }

    public function getFormClass(): string
    {
        return TaskForm::class;
    }
}