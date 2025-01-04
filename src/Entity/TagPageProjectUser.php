<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
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
    #[ORM\JoinColumn(nullable: false)]
    private ?TagPage $tagPage = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
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

    public function hasUserAccess(User $user): bool
    {
        return $this->getTagPage()->hasUserAccess($user)  && $this->getProjectUser()->hasUserAccess($user);
    }

    public function initialize(): static
    {
        return $this;
    }
}
