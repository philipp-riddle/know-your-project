<?php

namespace App\Entity\Thread;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Repository\ThreadItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadItemRepository::class)]
class ThreadItem implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'threadItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Thread $thread = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'threadItem', cascade: ['persist', 'remove'])]
    private ?ThreadItemPrompt $itemPrompt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'threadItem', cascade: ['persist', 'remove'])]
    private ?ThreadItemComment $threadItemComment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(?Thread $thread): static
    {
        $this->thread = $thread;

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

    public function getItemPrompt(): ?ThreadItemPrompt
    {
        return $this->itemPrompt;
    }

    public function setItemPrompt(ThreadItemPrompt $itemPrompt): static
    {
        // set the owning side of the relation if necessary
        if ($itemPrompt->getThreadItem() !== $this) {
            $itemPrompt->setThreadItem($this);
        }

        $this->itemPrompt = $itemPrompt;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->getThread()->hasUserAccess($user);
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();
        $this->updatedAt = new \DateTime();

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

    public function getThreadItemComment(): ?ThreadItemComment
    {
        return $this->threadItemComment;
    }

    public function setThreadItemComment(ThreadItemComment $threadItemComment): static
    {
        // set the owning side of the relation if necessary
        if ($threadItemComment->getThreadItem() !== $this) {
            $threadItemComment->setThreadItem($this);
        }

        $this->threadItemComment = $threadItemComment;

        return $this;
    }
}
