<?php

namespace App\Entity;

use App\Entity\Interface\UserPermissionInterface;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class, orphanRemoval: true)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectUser::class, orphanRemoval: true)]
    private Collection $projectUsers;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Page::class, orphanRemoval: true)]
    private Collection $pages;

    /**
     * @var Collection<int, ProjectUserEmailInvitation>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectUserEmailInvitation::class, orphanRemoval: true)]
    private Collection $projectUserEmailInvitations;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->projectUsers = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->projectUserEmailInvitations = new ArrayCollection();
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

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setProject($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        if ($this->getOwner() === $user) {
            return true;
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

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setProject($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getProject() === $this) {
                $page->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectUserEmailInvitation>
     */
    public function getProjectUserEmailInvitations(): Collection
    {
        return $this->projectUserEmailInvitations;
    }

    public function addProjectUserEmailInvitation(ProjectUserEmailInvitation $projectUserEmailInvitation): static
    {
        if (!$this->projectUserEmailInvitations->contains($projectUserEmailInvitation)) {
            $this->projectUserEmailInvitations->add($projectUserEmailInvitation);
            $projectUserEmailInvitation->setProject($this);
        }

        return $this;
    }

    public function removeProjectUserEmailInvitation(ProjectUserEmailInvitation $projectUserEmailInvitation): static
    {
        if ($this->projectUserEmailInvitations->removeElement($projectUserEmailInvitation)) {
            // set the owning side to null (unless already changed)
            if ($projectUserEmailInvitation->getProject() === $this) {
                $projectUserEmailInvitation->setProject(null);
            }
        }

        return $this;
    }
}
