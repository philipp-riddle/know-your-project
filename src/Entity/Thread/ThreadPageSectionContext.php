<?php

namespace App\Entity\Thread;

use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Page\PageSection;
use App\Entity\User\User;
use App\Repository\ThreadPageSectionContextRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadPageSectionContextRepository::class)]
class ThreadPageSectionContext implements UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pageSectionContext', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Thread $thread = null;

    #[ORM\OneToOne(inversedBy: 'threadContext', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(Thread $thread): static
    {
        $this->thread = $thread;

        return $this;
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
}
