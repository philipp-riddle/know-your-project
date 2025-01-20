<?php

namespace App\Service;

use App\Entity\Project\Project;
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
    public function getTasks(Project $project, ?string $workflowStepType = null): array
    {
        $filters = ['project' => $project];

        if (null !== $workflowStepType) {
            $filters['stepType'] = $workflowStepType;
        }

        return $this->taskRepository->findBy(
            $filters,
            ['orderIndex' => 'ASC'],
        );
    }
}