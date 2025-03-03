<?php

namespace App\Entity\Page;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Project\Project;
use App\Entity\Tag\TagPage;
use App\Entity\Task;
use App\Entity\User\User;
use App\Exception\Entity\EntityValidationException;
use App\Repository\PageRepository;
use App\Serializer\Attribute\IgnoreWhenNested;
use App\Service\File\Interface\EntityMultipleFileInterface;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Page extends CachedEntityVectorEmbedding
implements
    UserPermissionInterface,
    CrudEntityInterface,
    CrudEntityValidationInterface,
    EntityMultipleFileInterface,
    OrderListItemInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'set null')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
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

    #[ORM\Column(nullable: true)]
    private ?int $orderIndex = null;

    /**
     * @var Collection<int, PageUser>
     */
    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageUser::class, orphanRemoval: true)]
    private Collection $users;

    public function __construct()
    {
        $this->pageTabs = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->users = new ArrayCollection();
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
    #[IgnoreWhenNested]
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
        return $this->task ?? null;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

        return $this;
    }

    /**
     * @return TagPage[]
     */
    public function getTags(): array|Collection
    {
        // order tags by name, ascending
        return $this->tags;
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

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(?int $orderIndex): static
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    // === IMPLEMENTATION OF interface methods =======

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
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
            throw new EntityValidationException('Page must either have a project or a user connected');
        }

        if (\count($this->getPageTabs()) === 0) {
            throw new EntityValidationException('Page must have at least one tab');
        }
    }

    public function getTextForEmbedding(): ?string
    {
        $pageHtml = \sprintf('<h1>%s</h1>', $this->getName()); // The page name is the title of the page

        foreach ($this->getTags() as $tagPage) {
            $pageHtml .= \sprintf('<span>Has Tag %s</span>', $tagPage->getTag()->getName());
        }

        foreach ($this->getPageTabs()[0]?->getPageSections() ?? [] as $section) {
            if (!$section instanceof PageSectionEmbeddedPage) {
                if (null !== $textForEmbedding = $section->getTextForEmbedding()) {
                    $pageHtml .= '<section>';
                    $pageHtml .= $textForEmbedding;
                    $pageHtml .= '</section>';
                }
            }

        }

        return $pageHtml;
    }

    public function getTitleForSearchResult(): ?string
    {
        return $this->getName();
    }

    public function getMetaAttributes(): array
    {
        $attributes = [
            ...$this->getProject()->getMetaAttributes(), // inherit project meta attributes
            'page' => $this->getId(),
            'tags' => \array_map(fn(TagPage $tag) => $tag->getTag()->getId(), \iterator_to_array($this->getTags())),
        ];

        if (null !== ($this->task ?? null))  {
            $attributes['task'] = $this->task->getId();
        }

        return $attributes;
    }

    public function getParentEntities(): PersistentCollection|array
    {
        return []; // page has no parent entities; is top-level
    }

    public function getChildEntities(): PersistentCollection|array
    {
        // when a page is deleted, the page sections should be deleted as well from the embedding db
        return $this->getPageTabs()[0]?->getPageSections() ?? [];
    }

    /**
     * Returns all files for the associated page.
     * This includes all files from all page sections, i.e. the uploaded files.
     */
    public function getFiles(): array
    {
        $files = [];

        foreach ($this->getPageTabs() as $pageTab) {
            foreach ($pageTab->getPageSections() as $pageSection) {
                if (null !== $pageSectionFile = $pageSection->getPageSectionUpload()?->getFile()) {
                    $files[] = $pageSectionFile;
                }
            }
        }

        return $files;
    }

    /**
     * @return Collection<int, PageUser>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(PageUser $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setPage($this);
        }

        return $this;
    }

    public function removeUser(PageUser $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getPage() === $this) {
                $user->setPage(null);
            }
        }

        return $this;
    }
}
