<?php

namespace App\Entity\Tag;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Page\Page;
use App\Entity\User\User;
use App\Repository\TagPageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagPageRepository::class)]
class TagPage implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\ManyToOne(inversedBy: 'tags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    /**
     * @var Collection<int, TagPageUser>
     */
    #[ORM\OneToMany(mappedBy: 'tagPage', targetEntity: TagPageProjectUser::class, orphanRemoval: true)]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

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

    public function hasUserAccess(User $user): bool
    {
        return $this->getTag()->hasUserAccess($user) 
            && $this->getPage()->hasUserAccess($user);
    }

    public function initialize(): static
    {
        // nothing to initialize in this entity

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Collection<int, TagPageProjectUser>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(TagPageProjectUser $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setTagPage($this);
        }

        return $this;
    }

    public function removeUser(TagPageProjectUser $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getTagPage() === $this) {
                $user->setTagPage(null);
            }
        }

        return $this;
    }
}
