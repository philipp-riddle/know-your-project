<?php

namespace App\Entity\Embedding;

use App\Repository\EntityEmbeddingQueueItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntityEmbeddingQueueItemRepository::class)]
class EntityEmbeddingQueueItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    private ?string $text = null;

    #[ORM\Column]
    private array $metaAttributes = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?bool $isDeletion = null;

    /**
     * Property to store a nice name for the entity.
     * This is useful in the queue command and in the database to identify the entities that are processed/stored in the queue.
     */
    #[ORM\Column(length: 255)]
    private ?string $niceName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getMetaAttributes(): array
    {
        return $this->metaAttributes;
    }

    public function setMetaAttributes(array $metaAttributes): static
    {
        $this->metaAttributes = $metaAttributes;

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

    public function isDeletion(): ?bool
    {
        return $this->isDeletion;
    }

    public function setDeletion(bool $isDeletion): static
    {
        $this->isDeletion = $isDeletion;

        return $this;
    }

    public function getNiceName(): ?string
    {
        return $this->niceName;
    }

    public function setNiceName(string $niceName): static
    {
        $this->niceName = $niceName;

        return $this;
    }
}
