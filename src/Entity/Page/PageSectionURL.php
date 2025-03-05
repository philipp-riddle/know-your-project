<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Exception\Entity\EntityValidationException;
use App\Repository\PageSectionURLRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionURLRepository::class)]
class PageSectionURL implements UserPermissionInterface, CrudEntityValidationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionURL', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PageSection $pageSection = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $faviconUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isInitialized = null;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFaviconUrl(): ?string
    {
        return $this->faviconUrl;
    }

    public function setFaviconUrl(?string $faviconUrl): static
    {
        $this->faviconUrl = $faviconUrl;

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return true;
    }

    public function getCoverImageUrl(): ?string
    {
        return $this->coverImageUrl;
    }

    public function setCoverImageUrl(?string $coverImageUrl): static
    {
        $this->coverImageUrl = $coverImageUrl;

        return $this;
    }

    public function getIsInitialized(): ?bool
    {
        return $this->isInitialized;
    }

    public function setInitialized(?bool $isInitialized): static
    {
        $this->isInitialized = $isInitialized;

        return $this;
    }

    public function getTextForEmbedding(): string
    {
        $htmlText = '';
        $htmlText .= \sprintf('<h1>%s</h1>', $this->getUrl());

        if (!\in_array($this->getName(), ['', null, 'URL'], true)) {
            $htmlText .= \sprintf('<p>%s</p>', $this->getName());
        }

        if (null !== $this->getDescription()) {
            $htmlText .= \sprintf('<p>%s</p>', $this->getDescription());
        }

        if (null !== $this->getFaviconUrl()) {
            $htmlText .= \sprintf('<img src="%s" alt="Favicon">', $this->getFaviconUrl());
        }

        if (null !== $this->getCoverImageUrl()) {
            $htmlText .= \sprintf('<img src="%s" alt="Cover image">', $this->getCoverImageUrl());
        }

        return $htmlText;
    }

    public function validate(): void
    {
        $url = \trim($this->getUrl());

        if ($url !== '' && !\filter_var($url, FILTER_VALIDATE_URL)) {
            throw new EntityValidationException('URL is not valid: '.$url);
        }
    }
}
