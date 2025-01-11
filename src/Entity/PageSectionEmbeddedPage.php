<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageSectionEmbeddedPageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[ORM\Entity(repositoryClass: PageSectionEmbeddedPageRepository::class)]
class PageSectionEmbeddedPage implements UserPermissionInterface, CrudEntityInterface, CrudEntityValidationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'embeddedPage', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
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

    public function hasUserAccess(User $user): bool
    {
        return $this->pageSection->hasUserAccess($user, checkSubTypes: false) && ($this->page?->hasUserAccess($user) ?? true);
    }

    public function initialize(): static
    {
        return $this;
    }

    public function validate(): void
    {
        if ($this->page === $this->getpageSection()->getPageTab()->getPage()) {
            throw new BadRequestHttpException('Cannot embed the page itself.');
        }
    }
}
