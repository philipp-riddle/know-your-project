<?php

namespace App\Entity\Calendar;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Page\PageSectionCalendarEvent;
use App\Entity\Project\Project;
use App\Entity\Tag\TagCalendarEvent;
use App\Entity\User\User;
use App\Repository\CalendarEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalendarEventRepository::class)]
class CalendarEvent implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, TagCalendarEvent>
     */
    #[ORM\OneToMany(mappedBy: 'calendarEvent', targetEntity: TagCalendarEvent::class, orphanRemoval: true)]
    private Collection $eventTags;

    /**
     * @var Collection<int, PageSectionCalendarEvent>
     */
    #[ORM\OneToMany(mappedBy: 'calendarEvent', targetEntity: PageSectionCalendarEvent::class)]
    private Collection $pageSectionCalendarEvents;

    public function __construct()
    {
        $this->eventTags = new ArrayCollection();
        $this->pageSectionCalendarEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, TagCalendarEvent>
     */
    public function getEventTags(): Collection
    {
        return $this->eventTags;
    }

    public function addEventTag(TagCalendarEvent $eventTag): static
    {
        if (!$this->eventTags->contains($eventTag)) {
            $this->eventTags->add($eventTag);
            $eventTag->setCalendarEvent($this);
        }

        return $this;
    }

    public function removeEventTag(TagCalendarEvent $eventTag): static
    {
        if ($this->eventTags->removeElement($eventTag)) {
            // set the owning side to null (unless already changed)
            if ($eventTag->getCalendarEvent() === $this) {
                $eventTag->setCalendarEvent(null);
            }
        }

        return $this;
    }

    // === IMPLEMENTATION METHODS ==============

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        if (!$this->getProject()->hasUserAccess($user, $accessContext)) {
            return false;
        }

        foreach ($this->getEventTags() as $eventTag) {
            if (!$eventTag->getTag()->hasUserAccess($user, $accessContext)) {
                return false;
            }
        }

        return true;
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, PageSectionCalendarEvent>
     */
    public function getPageSectionCalendarEvents(): Collection
    {
        return $this->pageSectionCalendarEvents;
    }

    public function addPageSectionCalendarEvent(PageSectionCalendarEvent $pageSectionCalendarEvent): static
    {
        if (!$this->pageSectionCalendarEvents->contains($pageSectionCalendarEvent)) {
            $this->pageSectionCalendarEvents->add($pageSectionCalendarEvent);
            $pageSectionCalendarEvent->setCalendarEvent($this);
        }

        return $this;
    }

    public function removePageSectionCalendarEvent(PageSectionCalendarEvent $pageSectionCalendarEvent): static
    {
        if ($this->pageSectionCalendarEvents->removeElement($pageSectionCalendarEvent)) {
            // set the owning side to null (unless already changed)
            if ($pageSectionCalendarEvent->getCalendarEvent() === $this) {
                $pageSectionCalendarEvent->setCalendarEvent(null);
            }
        }

        return $this;
    }
}
