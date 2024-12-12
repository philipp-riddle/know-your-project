<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Page implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, PageTab>
     */
    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageTab::class, orphanRemoval: true)]
    private Collection $pageTabs;

    #[ORM\OneToOne(mappedBy: 'page')]
    private ?Task $task;

    public function __construct()
    {
        $this->pageTabs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PageTab>
     */
    public function getPageTabs(): Collection
    {
        return $this->pageTabs;
    }

    public function addPageTab(PageTab $pageTab): static
    {
        if (!$this->pageTabs->contains($pageTab)) {
            $this->pageTabs->add($pageTab);
            $pageTab->setPage($this);
        }

        return $this;
    }

    public function removePageTab(PageTab $pageTab): static
    {
        if ($this->pageTabs->removeElement($pageTab)) {
            // set the owning side to null (unless already changed)
            if ($pageTab->getPage() === $this) {
                $pageTab->setPage(null);
            }
        }

        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

        return $this;
    }

    /**
     * This is useful when creating many tabs at once without defining the name; creates names like 'Tab 1', 'Tab 2', etc.
     */
    public function getNewTabNameWithNumber(): string
    {
        $maxNumber = 0;

        foreach ($this->getPageTabs() as $pageTab) {
            $pageTabNameParts = explode(' ', $pageTab->getName());

            if (count($pageTabNameParts) < 2 || !\is_int($pageTabNameParts[1])) {
                continue;
            }

            $number = (int) $pageTabNameParts[1];
            $maxNumber = \max($maxNumber, $number);
        }

        return 'Tab ' . ($maxNumber + 1);

    }

    public function hasUserAccess(User $user): bool
    {
        $this->validate();

        return $this->user?->getId() === $user->getId() || $this->project?->hasUserAccess($user);
    }

    public function initialize(): static
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    #[ORM\PostUpdate]
    #[ORM\PostPersist]
    public function validate()
    {
        if (null === $this->getUser() && null === $this->getProject()) {
            throw new \RuntimeException('Page must either have a project or a user connected');
        }

        // if (\count($this->getPageTabs()) === 0) {
        //     throw new \RuntimeException('Page must have at least one tab');
        // }
    }
}
