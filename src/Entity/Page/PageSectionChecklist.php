<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Repository\PageSectionChecklistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionChecklistRepository::class)]
class PageSectionChecklist implements UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionChecklist', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, PageSectionChecklistItem>
     */
    #[ORM\OneToMany(mappedBy: 'pageSectionChecklist', targetEntity: PageSectionChecklistItem::class, orphanRemoval: true)]
    private Collection $pageSectionChecklistItems;

    public function __construct()
    {
        $this->pageSectionChecklistItems = new ArrayCollection();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, PageSectionChecklistItem>
     */
    public function getPageSectionChecklistItems(): Collection
    {
        return $this->pageSectionChecklistItems;
    }

    public function addPageSectionChecklistItem(PageSectionChecklistItem $pageSectionChecklistItem): static
    {
        if (!$this->pageSectionChecklistItems->contains($pageSectionChecklistItem)) {
            $this->pageSectionChecklistItems->add($pageSectionChecklistItem);
            $pageSectionChecklistItem->setPageSectionChecklist($this);
        }

        return $this;
    }

    public function removePageSectionChecklistItem(PageSectionChecklistItem $pageSectionChecklistItem): static
    {
        if ($this->pageSectionChecklistItems->removeElement($pageSectionChecklistItem)) {
            // set the owning side to null (unless already changed)
            if ($pageSectionChecklistItem->getPageSectionChecklist() === $this) {
                $pageSectionChecklistItem->setPageSectionChecklist(null);
            }
        }

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return $this->getPageSection()->hasUserAccess($user, $accessContext, checkSubTypes: false);
    }
}
