<?php

namespace App\Entity\Tag;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Repository\TagPageProjectUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagPageProjectUserRepository::class)]
class TagPageProjectUser implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?TagPage $tagPage = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProjectUser $projectUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTagPage(): ?TagPage
    {
        return $this->tagPage;
    }

    public function setTagPage(?TagPage $tagPage): static
    {
        $this->tagPage = $tagPage;

        return $this;
    }

    public function getProjectUser(): ?ProjectUser
    {
        return $this->projectUser;
    }

    public function setProjectUser(?ProjectUser $projectUser): static
    {
        $this->projectUser = $projectUser;

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        // anyone who has access to the tag + page and is in the project can add, edit or delete any tag page project users.
        // this means anyone in the project can assign users to tasks without permission problems.
        return $this->getTagPage()->hasUserAccess($user)  && $this->getProjectUser()->getProject()->isUserInProject($user);
    }

    public function initialize(): static
    {
        return $this;
    }
}
