<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Task;
use App\Repository\TaskRepository;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository,
        private PageService $pageService,
        private OrderListHandler $orderListHandler,
    ) { }

    public function addTaskToWorkflowStep(Task $task, string $workflowStepType): void
    {
        $task->setStepType($workflowStepType);
        $tasks = $this->getTasks($task->getProject(), $workflowStepType);
        $this->orderListHandler->add($task, $tasks);

        if (null === $task->getPage()) {
            $task->setPage($this->pageService->createDefaultPage($task));
        }
    }

    public function changeTaskOrder(Project $project, string $workflowStepType, array $idOrder): array
    {
        $tasks = $this->getTasks($project, $workflowStepType); // these are all the tasks we want to change the order for
        $this->orderListHandler->applyIdOrder($tasks, $idOrder);

        return $tasks;
    }

    /**
     * @return Task[]
     */
    public function getTasks(Project $project, string $workflowStepType): array
    {
        return $this->taskRepository->findBy(
            ['project' => $project, 'stepType' => $workflowStepType],
            ['orderIndex' => 'ASC'],
        );
    }
}