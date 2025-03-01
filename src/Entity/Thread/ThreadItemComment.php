<?php

namespace App\Entity\Thread;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Repository\ThreadItemCommentRepository;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: ThreadItemCommentRepository::class)]
class ThreadItemComment extends CachedEntityVectorEmbedding implements UserPermissionInterface, CrudEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'threadItemComment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThreadItem $threadItem = null;

    #[ORM\Column(length: 255)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThreadItem(): ?ThreadItem
    {
        return $this->threadItem;
    }

    public function setThreadItem(ThreadItem $threadItem): static
    {
        $this->threadItem = $threadItem;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function initialize(): static
    {
        return $this;
    }

    // === IMPLEMENTATION FUNCTIONS

    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool
    {
        return $this->threadItem->hasUserAccess($user);
    }

    public function getTextForEmbedding(): ?string
    {
        return null; // This class itself does not embed; instead the parent ThreadItem does
    }

    public function getTitleForSearchResult(): ?string
    {
        return null; // This class itself does not embed; instead the parent ThreadItem does
    }

    public function getMetaAttributes(): array
    {
        return [];
    }

    public function getParentEntities(): PersistentCollection|array
    {
        return [$this->threadItem];
    }

    public function getChildEntities(): PersistentCollection|array
    {
        return [];
    }
}
