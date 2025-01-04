<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\TagProjectUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagProjectUserRepository::class)]
class TagProjectUser implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\ManyToOne(inversedBy: 'tags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProjectUser $projectUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

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
        // either the user manages the tag itself or the user is the owner of the project and manages the tags for others
        return $this->getProjectUser()->getUser() === $user || $this->getProjectUser()->getProject()->getOwner() === $user;
    }

    public function initialize(): static
    {
        return $this;
    }
}
