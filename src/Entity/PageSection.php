<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageSectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
class PageSection implements UserPermissionInterface, CrudEntityInterface, OrderListItemInterface
{
    public const TYPE_COMMENT = 'comment';
    public const TYPE_CHECKLIST = 'checklist';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageSections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageTab $pageTab = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionText $pageSectionText = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionChecklist $pageSectionChecklist = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private ?int $orderIndex = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionURL $pageSectionURL = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionUpload $pageSectionUpload = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageTab(): ?PageTab
    {
        return $this->pageTab;
    }

    public function setPageTab(?PageTab $pageTab): static
    {
        $this->pageTab = $pageTab;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPageSectionText(): ?PageSectionText
    {
        return $this->pageSectionText;
    }

    public function setPageSectionText(PageSectionText $pageSectionText): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionText->getPageSection() !== $this) {
            $pageSectionText->setPageSection($this);
        }

        $this->pageSectionText = $pageSectionText;

        return $this;
    }

    public function getPageSectionChecklist(): ?PageSectionChecklist
    {
        return $this->pageSectionChecklist;
    }

    public function setPageSectionChecklist(PageSectionChecklist $pageSectionChecklist): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionChecklist->getPageSection() !== $this) {
            $pageSectionChecklist->setPageSection($this);
        }

        $this->pageSectionChecklist = $pageSectionChecklist;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->pageTab->hasUserAccess($user);
    }

    public function initialize(): static
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): static
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    public function getPageSectionURL(): ?PageSectionURL
    {
        return $this->pageSectionURL;
    }

    public function setPageSectionURL(PageSectionURL $pageSectionURL): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionURL->getPageSection() !== $this) {
            $pageSectionURL->setPageSection($this);
        }

        $this->pageSectionURL = $pageSectionURL;

        return $this;
    }

    public function getPageSectionUpload(): ?PageSectionUpload
    {
        return $this->pageSectionUpload;
    }

    public function setPageSectionUpload(PageSectionUpload $pageSectionUpload): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionUpload->getPageSection() !== $this) {
            $pageSectionUpload->setPageSection($this);
        }

        $this->pageSectionUpload = $pageSectionUpload;

        return $this;
    }
}
