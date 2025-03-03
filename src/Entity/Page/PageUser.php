<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Exception\Entity\EntityValidationException;
use App\Repository\PageUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageUserRepository::class)]
class PageUser implements UserPermissionInterface, CrudEntityInterface, CrudEntityValidationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    // === IMPLEMENTATION METHODS =====

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return $this->getPage()->hasUserAccess($user, $accessContext);
    }

    public function initialize(): static
    {
        return $this; // nothing to initialize; we still want this entity to be a CRUD entity though
    }

    public function validate(): void
    {
        foreach ($this->getPage()->getUsers() as $pageUser) {
            if ($pageUser !== $this && $pageUser->getUser() === $this->getUser()) {
                throw new EntityValidationException('User is already assigned to this page.');
            }
        }
    }
}
