<?php

namespace App\Entity\Project;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Tag\TagProjectUser;
use App\Entity\User\User;
use App\Repository\ProjectUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectUserRepository::class)]
class ProjectUser implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projectUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'projectUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    /**
     * @var Collection<int, TagProjectUser>
     */
    #[ORM\OneToMany(mappedBy: 'projectUser', targetEntity: TagProjectUser::class, orphanRemoval: true)]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        $isOwnerAccessContext = $accessContext === AccessContext::DELETE;
        $isOwner = $this->getProject()->getOwner() === $user;

        if ($isOwnerAccessContext || $isOwner) {
            return $isOwner;
        }

        return $this->getUser() === $user;
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, TagProjectUser>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(TagProjectUser $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setProjectUser($this);
        }

        return $this;
    }

    public function removeTag(TagProjectUser $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getProjectUser() === $this) {
                $tag->setProjectUser(null);
            }
        }

        return $this;
    }
}
