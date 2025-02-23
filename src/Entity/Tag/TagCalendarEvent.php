<?php

namespace App\Entity\Tag;

use App\Entity\Calendar\CalendarEvent;
use App\Entity\Tag\Tag;
use App\Repository\TagCalendarEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagCalendarEventRepository::class)]
class TagCalendarEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\ManyToOne(inversedBy: 'eventTags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CalendarEvent $calendarEvent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

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
}
