<?php

namespace App\Entity\Page;

use App\Entity\Calendar\CalendarEvent;
use App\Entity\Interface\AccessContext;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Page\PageSection;
use App\Entity\User\User;
use App\Repository\PageSectionCalendarEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionCalendarEventRepository::class)]
class PageSectionCalendarEvent implements UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'calendarEvent', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\ManyToOne(inversedBy: 'pageSectionCalendarEvents')]
    private ?CalendarEvent $calendarEvent = null;

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

    public function getCalendarEvent(): ?CalendarEvent
    {
        return $this->calendarEvent;
    }

    public function setCalendarEvent(?CalendarEvent $calendarEvent): static
    {
        $this->calendarEvent = $calendarEvent;

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return $this->pageSection->hasUserAccess($user, $accessContext, checkSubTypes: false) && ($this->calendarEvent?->hasUserAccess($user, $accessContext) ?? true);
    }
}
