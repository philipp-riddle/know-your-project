<?php

namespace App\Entity\User;

use App\Entity\File;
use App\Entity\Interface\AccessContext;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Repository\UserRepository;
use App\Serializer\Attribute\IgnoreWhenNested;
use App\Service\File\Interface\EntityFileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, UserPermissionInterface, EntityFileInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Project $selectedProject = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProjectUser::class)]
    private Collection $projectUsers;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?File $profilePicture = null;

    /**
     * @var Collection<int, UserInvitation>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserInvitation::class)]
    private Collection $userInvitations;

    public function __construct()
    {
        $this->projectUsers = new ArrayCollection();
        $this->userInvitations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    #[IgnoreWhenNested]
    public function getSelectedProject(): ?Project
    {
        return $this->selectedProject;
    }

    public function setSelectedProject(?Project $selectedProject): static
    {
        $this->selectedProject = $selectedProject;

        return $this;
    }

    /**
     * @return Collection|ProjectUser[]
     */
    #[IgnoreWhenNested]
    public function getProjectUsers(): Collection
    {
        return $this->projectUsers;
    }

    public function addProjectUser(ProjectUser $projectUser): static
    {
        if (!$this->projectUsers->contains($projectUser)) {
            $this->projectUsers[] = $projectUser;
            $projectUser->setUser($this);
        }

        return $this;
    }

    public function removeProjectUser(ProjectUser $projectUser): static
    {
        if ($this->projectUsers->removeElement($projectUser)) {
            // set the owning side to null (unless already changed)
            if ($projectUser->getUser() === $this) {
                $projectUser->setUser(null);
            }
        }

        return $this;
    }

    public function getProfilePicture(): ?File
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?File $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    // ==== IMPLEMENTATION METHODS ===============================

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        if ($this->getId() === $user->getId()) {
            return true;
        }

        if ($accessContext === AccessContext::READ) {
            foreach ($this->getProjectUsers() as $projectUser) {
                if ($projectUser->getProject()->hasUserAccess($user, AccessContext::READ)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getFile(): ?File
    {
        return $this->getProfilePicture(); // return the profile picture as a file of the user; allows for deletion of the profile picture if the user gets deleted
    }

    /**
     * @return Collection<int, UserInvitation>
     */
    public function getUserInvitations(): Collection
    {
        return $this->userInvitations;
    }

    public function addUserInvitation(UserInvitation $userInvitation): static
    {
        if (!$this->userInvitations->contains($userInvitation)) {
            $this->userInvitations->add($userInvitation);
            $userInvitation->setUser($this);
        }

        return $this;
    }

    public function removeUserInvitation(UserInvitation $userInvitation): static
    {
        if ($this->userInvitations->removeElement($userInvitation)) {
            // set the owning side to null (unless already changed)
            if ($userInvitation->getUser() === $this) {
                $userInvitation->setUser(null);
            }
        }

        return $this;
    }
}
