<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
class Thread implements UserPermissionInterface, CrudEntityInterface, CrudEntityValidationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'thread', cascade: ['persist', 'remove'])]
    private ?ThreadPageSectionContext $pageSectionContext = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ThreadItem>
     */
    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: ThreadItem::class, orphanRemoval: true)]
    private Collection $threadItems;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    public function __construct()
    {
        $this->threadItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageSectionContext(): ?ThreadPageSectionContext
    {
        return $this->pageSectionContext;
    }

    public function setPageSectionContext(ThreadPageSectionContext $pageSectionContext): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionContext->getThread() !== $this) {
            $pageSectionContext->setThread($this);
        }

        $this->pageSectionContext = $pageSectionContext;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ThreadItem>
     */
    public function getThreadItems(): Collection
    {
        return $this->threadItems;
    }

    public function addThreadItem(ThreadItem $threadItem): static
    {
        if (!$this->threadItems->contains($threadItem)) {
            $this->threadItems->add($threadItem);
            $threadItem->setThread($this);
        }

        return $this;
    }

    public function removeThreadItem(ThreadItem $threadItem): static
    {
        if ($this->threadItems->removeElement($threadItem)) {
            // set the owning side to null (unless already changed)
            if ($threadItem->getThread() === $this) {
                $threadItem->setThread(null);
            }
        }

        return $this;
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function validate(): void
    {
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->getPageSectionContext()->hasUserAccess($user);
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }
}
