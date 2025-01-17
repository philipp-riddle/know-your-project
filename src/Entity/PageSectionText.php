<?php

namespace App\Entity;

use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageSectionTextRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionTextRepository::class)]
class PageSectionText implements UserPermissionInterface
{
    public const MAX_CONTENT_LENGTH = 65535;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionText', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\Column(type: Types::TEXT, length: self::MAX_CONTENT_LENGTH)]
    private ?string $content = null;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return true;
    }
}
