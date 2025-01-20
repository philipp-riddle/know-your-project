<?php

namespace App\Entity\Thread;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Repository\ThreadItemCommentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadItemCommentRepository::class)]
class ThreadItemComment implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'threadItemComment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThreadItem $threadItem = null;

    #[ORM\Column(length: 255)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThreadItem(): ?ThreadItem
    {
        return $this->threadItem;
    }

    public function setThreadItem(ThreadItem $threadItem): static
    {
        $this->threadItem = $threadItem;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function initialize(): static
    {
        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->threadItem->hasUserAccess($user);
    }
}
