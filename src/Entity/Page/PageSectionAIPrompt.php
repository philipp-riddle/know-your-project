<?php

namespace App\Entity\Page;

use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Prompt;
use App\Entity\User\User;
use App\Repository\PageSectionAIPromptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionAIPromptRepository::class)]
class PageSectionAIPrompt implements UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'aiPrompt', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\ManyToOne]
    private ?Page $pageContext = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionAIPrompt', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Prompt $prompt = null;

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

    public function getPageContext(): ?Page
    {
        return $this->pageContext;
    }

    public function setPageContext(?Page $pageContext): static
    {
        $this->pageContext = $pageContext;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return ($this->pageContext?->hasUserAccess($user) ?? true);
    }

    public function getPrompt(): ?Prompt
    {
        return $this->prompt;
    }

    public function setPrompt(Prompt $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }
}
