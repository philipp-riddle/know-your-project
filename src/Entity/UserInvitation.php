<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\UserInvitationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: UserInvitationRepository::class)]
class UserInvitation implements CrudEntityInterface, UserPermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'UserInvitations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Project $project = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $email = null;

    // @todo this ignore does not work
    #[Ignore]
    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // attribute does not work but prefixing the method with a '_' works. meh.
    #[Ignore]
    public function _getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTimeImmutable();

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->getProject()?->getOwner() === $user;
    }
}