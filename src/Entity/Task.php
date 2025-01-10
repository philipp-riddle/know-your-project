<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\TaskRepository;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task extends CachedEntityVectorEmbedding implements OrderListItemInterface, CrudEntityInterface, UserPermissionInterface, CrudEntityValidationInterface
{
    public const STEP_TYPES = [
        'Discover',
        'Define',
        'Develop',
        'Deliver',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column]
    private ?bool $isArchived = null;

    #[ORM\Column]
    private ?int $orderIndex = null;

    #[ORM\Column(length: 255)]
    private ?string $stepType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

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

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function isIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): static
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    public function getStepType(): ?string
    {
        return $this->stepType;
    }

    public function setStepType(string $stepType): static
    {
        $this->stepType = $stepType;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function hasUserAccess(User $user): bool
    {
        return $this->getProject()?->hasUserAccess($user) ?? true;
    }

    public function initialize(): static
    {
        $this->isArchived ??= false;

        return $this;
    }

    public function validate(): void
    {
        if (!\in_array($this->stepType, self::STEP_TYPES, true)) {
            throw new BadRequestHttpException('Invalid step type');
        }
    }

    public function getTextForEmbedding(): string
    {
        $lines = [
            \sprintf('Task (%d): %s', $this->getId(), $this->getStepType()),
            'Order Index: ' . $this->getOrderIndex(),
        ];

        if ($this->getDueDate() !== null) {
            $lines[] = \sprintf('Due: %s', $this->getDueDate()->format('Y-m-d'));
        }

        if ($this->isArchived) {
            $lines[] = 'Archived';
        }

        return \implode("\n", $lines);
    }

    public function getMetaAttributes(): array
    {
        return $this->getPage()->getMetaAttributes();
    }
}
