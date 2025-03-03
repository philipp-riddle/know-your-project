<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Exception\BadRequestException;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) { }

    /**
     * Adds a user to a project.
     */
    public function addUserToProject(User $user, Project $project): ProjectUser
    {
        if (null !== $project->getProjectUser($user)) {
            throw new BadRequestException('User is already in the project');
        }

        $projectUser = (new ProjectUser())
            ->initialize()
            ->setUser($user)
            ->setProject($project);
        $project->addProjectUser($projectUser);
        $this->em->persist($projectUser);

        return $projectUser;
    }
}