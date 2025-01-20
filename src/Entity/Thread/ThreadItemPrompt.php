<?php

namespace App\Entity\Thread;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Prompt;
use App\Entity\User\User;
use App\Repository\ThreadItemPromptRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadItemPromptRepository::class)]
class ThreadItemPrompt implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'itemPrompt', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThreadItem $threadItem = null;

    #[ORM\ManyToOne(inversedBy: 'threadItemPrompts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prompt $prompt = null;

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

    public function getPrompt(): ?Prompt
    {
        return $this->prompt;
    }

    public function setPrompt(?Prompt $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->prompt->hasUserAccess($user);
    }

    public function initialize(): static
    {
        return $this;
    }
}
