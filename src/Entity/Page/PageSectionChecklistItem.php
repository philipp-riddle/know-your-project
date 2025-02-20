<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Repository\PageSectionChecklistItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionChecklistItemRepository::class)]
class PageSectionChecklistItem implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageSectionChecklistItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSectionChecklist $pageSectionChecklist = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $complete = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageSectionChecklist(): ?PageSectionChecklist
    {
        return $this->pageSectionChecklist;
    }

    public function setPageSectionChecklist(?PageSectionChecklist $pageSectionChecklist): static
    {
        $this->pageSectionChecklist = $pageSectionChecklist;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isComplete(): ?bool
    {
        return $this->complete;
    }

    public function setComplete(bool $complete): static
    {
        $this->complete = $complete;

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return $this->getPageSectionChecklist()->hasUserAccess($user);
    }

    public function initialize(): static
    {
        return $this;
    }
}
