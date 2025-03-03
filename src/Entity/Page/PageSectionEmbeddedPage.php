<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Exception\Entity\EntityValidationException;
use App\Repository\PageSectionEmbeddedPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionEmbeddedPageRepository::class)]
class PageSectionEmbeddedPage implements UserPermissionInterface, CrudEntityInterface, CrudEntityValidationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'embeddedPage', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PageSection $pageSection = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Page $page = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageSection(): ?PageSection
    {
        return $this->pageSection;
    }

    public function setPageSection(PageSection $pageSection): static
    {
        $this->pageSection = $pageSection;

        return $this;
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

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return ($this->page?->hasUserAccess($user) ?? true);
    }

    public function initialize(): static
    {
        return $this;
    }

    public function validate(): void
    {
        if ($this->page === $this->getpageSection()->getPageTab()->getPage()) {
            throw new EntityValidationException('Cannot embed the page itself.');
        }
    }
}
