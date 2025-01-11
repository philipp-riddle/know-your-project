<?php

namespace App\Entity;

use App\Entity\Interface\UserPermissionInterface;
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

    #[ORM\Column(type: Types::TEXT, length: 1024, nullable: false)]
    private ?string $prompt = null;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    private ?string $responseText = null;

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

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getResponseText(): ?string
    {
        return $this->responseText;
    }

    public function setResponseText(string $responseText): static
    {
        $this->responseText = $responseText;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->pageSection->hasUserAccess($user, checkSubTypes: false) && ($this->pageContext?->hasUserAccess($user) ?? true);
    }
}
