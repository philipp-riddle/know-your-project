<?php

namespace App\Entity;

use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageSectionSummaryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionSummaryRepository::class)]
class PageSectionSummary implements UserPermissionInterface
{
    public const MAX_SUMMARY_LENGTH = 16384;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionSummary', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionSummary', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
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

    public function hasUserAccess(User $user): bool
    {
        return $this->pageSection->hasUserAccess($user, checkSubTypes: false);
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
