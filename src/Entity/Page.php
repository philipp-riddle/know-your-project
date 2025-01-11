<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageRepository;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Page extends CachedEntityVectorEmbedding implements UserPermissionInterface, CrudEntityInterface, CrudEntityValidationInterface
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

    /**
     * @var Collection<int, TagPage>
     */
    #[ORM\OneToMany(mappedBy: 'page', targetEntity: TagPage::class, orphanRemoval: true)]
    private Collection $tags;

    public function __construct()
    {
        $this->pageTabs = new ArrayCollection();
        $this->tags = new ArrayCollection();
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
     * @return TagPage[]
     */
    public function getTags(): array
    {
        // order tags by name, ascending
        $tags = $this->tags->toArray();
        \usort($tags, fn(TagPage $a, TagPage $b) => $a->getTag()->getName() <=> $b->getTag()->getName());

        return $tags;
    }

    public function addTag(TagPage $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setPage($this);
        }

        return $this;
    }

    public function removeTag(TagPage $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getPage() === $this) {
                $tag->setPage(null);
            }
        }

        return $this;
    }

    // === IMPLEMENTATION OF interface methods =======

    public function hasUserAccess(User $user): bool
    {
        $this->validate();

        return $this->user?->getId() === $user->getId() || $this->project?->hasUserAccess($user);
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();

        return $this;
    }

    public function validate(): void
    {
        if (null === $this->getUser() && null === $this->getProject()) {
            throw new \RuntimeException('Page must either have a project or a user connected');
        }

        if (\count($this->getPageTabs()) === 0) {
            throw new \RuntimeException('Page must have at least one tab');
        }
    }

    public function getTextForEmbedding(): ?string
    {
        return $this->getName();
    }

    public function getMetaAttributes(): array
    {
        $attributes = [
            ...$this->getProject()->getMetaAttributes(), // inherit project meta attributes
            'page' => $this->getId(),
            'tags' => \array_map(fn(TagPage $tag) => $tag->getTag()->getId(), $this->getTags()),
        ];

        if (null !== ($this->task ?? null))  {
            $attributes['task'] = $this->task->getId();
        }

        return $attributes;
    }
}
