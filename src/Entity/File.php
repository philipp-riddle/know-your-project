<?php

namespace App\Entity;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\Page\PageSectionUpload;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Repository\FileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $relativePath = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'file', cascade: ['persist', 'remove'])]
    private ?PageSectionUpload $pageSectionUpload = null;

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

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

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

    public function _getRelativePath(): ?string
    {
        return $this->relativePath;
    }

    public function setRelativePath(string $relativePath): static
    {
        $this->relativePath = $relativePath;

        return $this;
    }

    public function getPublicFilePath(): ?string
    {
        $isPublic = \str_contains($this->relativePath, '/public');

        if ($isPublic) {
            return \str_replace('/public', '', $this->relativePath);
        }

        return null; // not public; thus no direct access
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

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        $isFileOwnerAccessContext = \in_array($accessContext, [AccessContext::DELETE, AccessContext::UPDATE], true);

        // the file owner can always delete and update the file
        if ($isFileOwnerAccessContext) {
            return $user === $this->getUser();
        }

        // anyone in the project can download & read files of other project users
        $projectMemberAllowedContexts = [AccessContext::READ, AccessContext::DOWNLOAD];

        // pass the check if the user has access to the file owner;
        // the check in the User class checks all of the user's projects and if the accessing user is in any of them.
        if (\in_array($accessContext, $projectMemberAllowedContexts, true) && $this->getUser()->hasUserAccess($user, $accessContext)) {
            return true;
        }

        // everything else (on the file-basis) is limited to the project owner and the user, such as deleting
        return (null !== $this->getProject() && $user === $this->getProject()->getOwner()) || $this->getUser() === $user;
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();

        return $this;
    }

    public function getPageSectionUpload(): ?PageSectionUpload
    {
        return $this->pageSectionUpload;
    }

    public function setPageSectionUpload(PageSectionUpload $pageSectionUpload): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionUpload->getFile() !== $this) {
            $pageSectionUpload->setFile($this);
        }

        $this->pageSectionUpload = $pageSectionUpload;

        return $this;
    }
}
