<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Repository\PageSectionURLRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionURLRepository::class)]
class PageSectionURL implements UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionURL', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

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

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return true;
    }
}
