<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Form\MoveTaskForm;
use App\Form\TaskForm;
use App\Repository\TaskRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\TaskService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/task')]
class TaskApiController extends ApiController
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
        if (!$task->getProject()->hasUserAccess($this->getUser())) {
            return $this->json(['error' => 'You do not have access to the specified task'], 403);
        }

        return $this->jsonSerialize($task);
    }

    #[Route('/list/{workflowStepType}', name: 'api_task_list', methods: ['GET'])]
    public function getTasks(string $workflowStepType): JsonResponse
    {
        return $this->jsonSerialize(
            $this->taskService->getTasks($this->getUser()->getSelectedProject(), $workflowStepType)
        );
    }

    #[Route('/{workflowStepType}', name: 'api_task_create', methods: ['POST'])]
    public function createTask(string $workflowStepType, Request $request): JsonResponse
    {
        $createTaskForm = $this->createForm(TaskForm::class);
        $createTaskForm->submit($request->toArray());

        if (!$createTaskForm->isSubmitted() || !$createTaskForm->isValid()) {
            return $this->json($createTaskForm->getErrors(true, true), 400);
        }

        /** @var Task $task */
        $task = $createTaskForm->getData();
        $task
            ->setProject($this->getUser()->getSelectedProject())
            ->setStepType($workflowStepType)
            ->setIsArchived(false);
        $this->taskService->addTaskToWorkflowStep($task, $workflowStepType);

        $this->em->persist($task);
        $this->em->flush();

        return $this->jsonSerialize($this->taskService->getTasks($this->getUser()->getSelectedProject(), $workflowStepType));
    }

    #[Route('/{task}', name: 'api_task_update', methods: ['PUT'])]
    public function updateTask(Task $task, Request $request): JsonResponse
    {
        if (!$task->getProject()->hasUserAccess($this->getUser())) {
            return $this->json(['error' => 'You do not have access to the specified task'], 403);
        }

        $updateTaskForm = $this->createForm(TaskForm::class, $task);
        $updateTaskForm->submit($request->toArray());

        if (!$updateTaskForm->isSubmitted() || !$updateTaskForm->isValid()) {
            return $this->json($updateTaskForm->getErrors(true, true), 400);
        }

        /** @var Task $task */
        $task = $updateTaskForm->getData();

        if (!$task->getProject()->hasUserAccess($this->getUser())) {
            return $this->json(['error' => 'You do not have access to the updated project of the task!'], 403);
        }

        $this->em->flush();

        return $this->jsonSerialize($task);
    }

    #[Route('/{task}', name: 'api_task_delete', methods: ['DELETE'])]
    public function deleteTask(Task $task): JsonResponse
    {
        if (!$task->getProject()->hasUserAccess($this->getUser())) {
            return $this->json(['error' => 'You do not have access to the specified task'], 403);
        }

        $this->em->remove($task);
        $this->em->flush();

        return $this->json(true);
    }

    #[Route('/{workflowStepType}/order', name: 'api_task_order', methods: ['POST'])]
    public function changeTaskOrder(string $workflowStepType, Request $request)
    {
        $content = $request->toArray();
        
        if (!\array_key_exists('idOrder', $content) || !\is_array($content['idOrder'])) {
            return $this->json(['error' => 'Invalid request body'], 400);
        }

        $idOrder = $content['idOrder'];
        $tasks = $this->taskService->changeTaskOrder($this->getUser()->getSelectedProject(), $workflowStepType, $idOrder);
    
        $this->em->flush();

        return $this->jsonSerialize($tasks);
    }

    #[Route('/{task}/move', name: 'api_task_move', methods: ['POST'])]
    public function moveTask(Task $task, Request $request)
    {
        if (!$task->getProject()->hasUserAccess($this->getUser())) {
            return $this->json(['error' => 'You do not have access to the specified project'], 403);
        }

        $originalStepType = $task->getStepType(); // we have to clone the value here before handling the form data

        $updateTaskForm = $this->createForm(MoveTaskForm::class, $task);
        $updateTaskForm->submit($request->toArray());

        if (!$updateTaskForm->isSubmitted() || !$updateTaskForm->isValid()) {
            return $this->json($updateTaskForm->getErrors(true, true), 400);
        }

        /** @var Task $task */
        $task = $updateTaskForm->getData();
        $newStepType = $task->getStepType();

        if ($originalStepType === $newStepType) {
            return $this->json([
                'error' => \sprintf(
                    'Already part of step "%s"; to change order use the /order endpoint',
                    $originalStepType,
                ),
            ], 400);
        }

        $this->taskService->addTaskToWorkflowStep($task, $newStepType);
        $this->em->flush();

        return $this->jsonSerialize($task);
    }
}