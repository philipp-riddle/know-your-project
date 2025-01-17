<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PromptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromptRepository::class)]
class Prompt implements UserPermissionInterface, CrudEntityInterface
{
    public const MAX_PROMPT_LENGTH = 8096;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: self::MAX_PROMPT_LENGTH)]
    private ?string $promptText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, length: 65535)]
    private ?string $responseText = null;

    #[ORM\Column(nullable: true)]
    private ?int $promptTokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $completionTokens = null;

    #[ORM\OneToOne(mappedBy: 'prompt', cascade: ['persist', 'remove'])]
    private ?PageSectionAIPrompt $pageSectionAIPrompt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ThreadItemPrompt>
     */
    #[ORM\OneToMany(mappedBy: 'prompt', targetEntity: ThreadItemPrompt::class, orphanRemoval: true)]
    private Collection $threadItemPrompts;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'prompt', cascade: ['persist', 'remove'])]
    private ?PageSectionSummary $pageSectionSummary = null;

    public function __construct()
    {
        $this->threadItemPrompts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPromptText(): ?string
    {
        return $this->promptText;
    }

    public function setPromptText(string $promptText): static
    {
        $this->promptText = \substr($promptText, 0, self::MAX_PROMPT_LENGTH);

        return $this;
    }

    public function getResponseText(): ?string
    {
        return $this->responseText;
    }

    public function setResponseText(?string $responseText): static
    {
        $this->responseText = $responseText;

        return $this;
    }

    public function getPromptTokens(): ?int
    {
        return $this->promptTokens;
    }

    public function setPromptTokens(?int $promptTokens): static
    {
        $this->promptTokens = $promptTokens;

        return $this;
    }

    public function getCompletionTokens(): ?int
    {
        return $this->completionTokens;
    }

    public function setCompletionTokens(?int $completionTokens): static
    {
        $this->completionTokens = $completionTokens;

        return $this;
    }

    public function getPageSectionAIPrompt(): ?PageSectionAIPrompt
    {
        return $this->pageSectionAIPrompt;
    }

    public function setPageSectionAIPrompt(PageSectionAIPrompt $pageSectionAIPrompt): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionAIPrompt->getPrompt() !== $this) {
            $pageSectionAIPrompt->setPrompt($this);
        }

        $this->pageSectionAIPrompt = $pageSectionAIPrompt;

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

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, ThreadItemPrompt>
     */
    public function getThreadItemPrompts(): Collection
    {
        return $this->threadItemPrompts;
    }

    public function addThreadItemPrompt(ThreadItemPrompt $threadItemPrompt): static
    {
        if (!$this->threadItemPrompts->contains($threadItemPrompt)) {
            $this->threadItemPrompts->add($threadItemPrompt);
            $threadItemPrompt->setPrompt($this);
        }

        return $this;
    }

    public function removeThreadItemPrompt(ThreadItemPrompt $threadItemPrompt): static
    {
        if ($this->threadItemPrompts->removeElement($threadItemPrompt)) {
            // set the owning side to null (unless already changed)
            if ($threadItemPrompt->getPrompt() === $this) {
                $threadItemPrompt->setPrompt(null);
            }
        }

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->getUser() === $user || $this->getProject()?->hasUserAccess($user);
    }

    public function getPageSectionSummary(): ?PageSectionSummary
    {
        return $this->pageSectionSummary;
    }

    public function setPageSectionSummary(PageSectionSummary $pageSectionSummary): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionSummary->getPrompt() !== $this) {
            $pageSectionSummary->setPrompt($this);
        }

        $this->pageSectionSummary = $pageSectionSummary;

        return $this;
    }
}
