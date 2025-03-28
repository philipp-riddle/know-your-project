<?php

namespace App\Entity\Project;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Tag\Tag;
use App\Entity\User\User;
use App\Entity\User\UserInvitation;
use App\Repository\ProjectRepository;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project extends CachedEntityVectorEmbedding implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectUser::class, orphanRemoval: true)]
    private Collection $projectUsers;

    /**
     * @var Collection<int, UserInvitation>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: UserInvitation::class, orphanRemoval: true)]
    private Collection $UserInvitations;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Tag::class, orphanRemoval: true)]
    private Collection $tags;

    public function __construct()
    {
        $this->projectUsers = new ArrayCollection();
        $this->UserInvitations = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        $isOwnerAction = $accessContext === AccessContext::DELETE;
        $isOwner = $this->getOwner() === $user;

        if ($isOwnerAction) {
            return $isOwner; // only the owner can delete the entire project
        }

        foreach ($this->getProjectUsers() as $projectUser) {
            if ($projectUser->getUser() === $user) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @return ProjectUser[]
     */
    public function getProjectUsers(): Collection
    {
        return $this->projectUsers;
    }

    public function getProjectUser(User $user): ?ProjectUser
    {
        foreach ($this->getProjectUsers() as $projectUser) {
            if ($projectUser->getUser() === $user) {
                return $projectUser;
            }
        }

        return null;
    }

    public function addProjectUser(ProjectUser $projectUser): static
    {
        if (!$this->projectUsers->contains($projectUser)) {
            $this->projectUsers->add($projectUser);
            $projectUser->setProject($this);
        }

        return $this;
    }

    public function removeProjectUser(ProjectUser $projectUser): static
    {
        if ($this->projectUsers->removeElement($projectUser)) {
            // set the owning side to null (unless already changed)
            if ($projectUser->getProject() === $this) {
                $projectUser->setProject(null);
            }
        }

        return $this;
    }

    public function isUserInProject(User $user): bool
    {
        foreach ($this->getProjectUsers() as $projectUser) {
            if ($projectUser->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, UserInvitation>
     */
    public function getUserInvitations(): Collection
    {
        return $this->UserInvitations;
    }

    public function addUserInvitation(UserInvitation $UserInvitation): static
    {
        if (!$this->UserInvitations->contains($UserInvitation)) {
            $this->UserInvitations->add($UserInvitation);
            $UserInvitation->setProject($this);
        }

        return $this;
    }

    public function removeUserInvitation(UserInvitation $UserInvitation): static
    {
        if ($this->UserInvitations->removeElement($UserInvitation)) {
            // set the owning side to null (unless already changed)
            if ($UserInvitation->getProject() === $this) {
                $UserInvitation->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setProject($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getProject() === $this) {
                $tag->setProject(null);
            }
        }

        return $this;
    }

    public function getTextForEmbedding(): ?string
    {
        return null;
    }

    public function getTitleForSearchResult(): ?string
    {
        return null;
    }

    public function getMetaAttributes(): array
    {
        return [
            'project' => $this->getId(),
            'user' => $this->getOwner()->getId(),
        ];
    }

    public function getParentEntities(): PersistentCollection|array
    {
        return []; // No parent entities; project is top-level
    }

    public function getChildEntities(): PersistentCollection|array
    {
        // @todo Implement; return all child entities which should be deleted when this project is deleted
        // there can be quite many pages
        return [];
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTimeImmutable();

        return $this;
    }
}
