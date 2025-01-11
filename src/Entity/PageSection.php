<?php

namespace App\Entity;

use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Repository\PageSectionRepository;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
class PageSection extends CachedEntityVectorEmbedding implements UserPermissionInterface, CrudEntityInterface, OrderListItemInterface, CrudEntityValidationInterface
{
    public const TYPE_COMMENT = 'comment';
    public const TYPE_CHECKLIST = 'checklist';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageSections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageTab $pageTab = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private ?int $orderIndex = null;

    // == all different page section types and their associated entities ==
    // == each page section can only have one of these ====================

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionText $pageSectionText = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionChecklist $pageSectionChecklist = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionURL $pageSectionURL = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionUpload $pageSectionUpload = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionEmbeddedPage $embeddedPage = null;

    #[ORM\OneToOne(mappedBy: 'pageSection', cascade: ['persist', 'remove'])]
    private ?PageSectionAIPrompt $aiPrompt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageTab(): ?PageTab
    {
        return $this->pageTab;
    }

    public function setPageTab(?PageTab $pageTab): static
    {
        $this->pageTab = $pageTab;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getPageSectionText(): ?PageSectionText
    {
        return $this->pageSectionText;
    }

    public function setPageSectionText(PageSectionText $pageSectionText): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionText->getPageSection() !== $this) {
            $pageSectionText->setPageSection($this);
        }

        $this->pageSectionText = $pageSectionText;

        return $this;
    }

    public function getPageSectionChecklist(): ?PageSectionChecklist
    {
        return $this->pageSectionChecklist;
    }

    public function setPageSectionChecklist(PageSectionChecklist $pageSectionChecklist): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionChecklist->getPageSection() !== $this) {
            $pageSectionChecklist->setPageSection($this);
        }

        $this->pageSectionChecklist = $pageSectionChecklist;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

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

    public function getPageSectionURL(): ?PageSectionURL
    {
        return $this->pageSectionURL;
    }

    public function setPageSectionURL(PageSectionURL $pageSectionURL): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionURL->getPageSection() !== $this) {
            $pageSectionURL->setPageSection($this);
        }

        $this->pageSectionURL = $pageSectionURL;

        return $this;
    }

    public function getPageSectionUpload(): ?PageSectionUpload
    {
        return $this->pageSectionUpload;
    }

    public function setPageSectionUpload(PageSectionUpload $pageSectionUpload): static
    {
        // set the owning side of the relation if necessary
        if ($pageSectionUpload->getPageSection() !== $this) {
            $pageSectionUpload->setPageSection($this);
        }

        $this->pageSectionUpload = $pageSectionUpload;

        return $this;
    }

    public function getEmbeddedPage(): ?PageSectionEmbeddedPage
    {
        return $this->embeddedPage;
    }

    public function setEmbeddedPage(PageSectionEmbeddedPage $embeddedPage): static
    {
        // set the owning side of the relation if necessary
        if ($embeddedPage->getPageSection() !== $this) {
            $embeddedPage->setPageSection($this);
        }

        $this->embeddedPage = $embeddedPage;

        return $this;
    }

    // === IMPLEMENTATION OF interface methods =======

    public function hasUserAccess(User $user, bool $checkSubTypes = true): bool
    {
        if (!$this->pageTab->hasUserAccess($user)) {
            return false;
        }


        if ($checkSubTypes) {
            if ($this->pageSectionText !== null && !$this->pageSectionText->hasUserAccess($user)) {
                return false;
            }

            if ($this->pageSectionChecklist !== null && !$this->pageSectionChecklist->hasUserAccess($user)) {
                return false;
            }

            if ($this->embeddedPage !== null && !$this->embeddedPage->hasUserAccess($user)) {
                return false;
            }

            if ($this->aiPrompt != null && !$this->aiPrompt->hasUserAccess($user)) {
                return false;
            }
        }

        return true;
    }

    public function initialize(): static
    {
        $this->createdAt ??= new \DateTime();
        $this->updatedAt ??= new \DateTime();

        return $this;
    }

    public function validate(): void
    {
        if ($this->pageSectionText === null && $this->pageSectionChecklist === null && $this->pageSectionURL === null && $this->pageSectionUpload === null && $this->embeddedPage === null && $this->aiPrompt === null) {
            throw new BadRequestHttpException('A page section must have a content type (text, checklist, URL, upload, embedded page, AI prompt)');
        }

        $pageSectionTypesNotNull = 0;

        if ($this->pageSectionText !== null) {
            $pageSectionTypesNotNull++;
        }

        if ($this->pageSectionChecklist !== null) {
            $pageSectionTypesNotNull++;
        }

        if ($this->pageSectionURL !== null) {
            $pageSectionTypesNotNull++;
        }

        if ($this->pageSectionUpload !== null) {
            $pageSectionTypesNotNull++;
        }

        if ($this->embeddedPage !== null) {
            $pageSectionTypesNotNull++;
            $this->embeddedPage->validate();
        }

        if ($this->aiPrompt != null) {
            $pageSectionTypesNotNull++;
        }

        if ($pageSectionTypesNotNull > 1) {
            throw new BadRequestHttpException('A page section must have only one content type');
        }
    }

    public function getTextForEmbedding(): ?string
    {
        if (null !== $text = $this->getPageSectionText()) {
            return $text->getContent();
        } else if (null !== $url = $this->getPageSectionURL()) {
            return $url->getUrl();
        } else if (null !== $embeddedPage = $this->getEmbeddedPage()) {
            if (null === $embeddedPage->getPage()) {
                return null;
            }

            return \sprintf('Embedded page: %s', $embeddedPage->getPage()->getId());
        } else if (null !== $upload = $this->getPageSectionUpload()) {
            return $upload->getFilename();
        } else if (null !== $checklist = $this->getPageSectionChecklist()) {
            $text = \sprintf('<p>Checklist: %s</p>', $checklist->getName());
            $text .= '<ul>';

            foreach ($checklist->getPageSectionChecklistItems() as $item) {
                $itemName = $item->getName();

                if ($item->isComplete()) {
                    $itemName = \sprintf('<s>%s</s> (complete)', $itemName);
                }

                $text .= $itemName;
            }

            $text .= '</ul>';

            return $text;
        } else if (null !== $aiPrompt = $this->getAiPrompt()) {
            if (null === $aiPrompt->getPrompt() && null === $aiPrompt->getResponseText()) {
                return null;
            }

            $text = '';

            if (null !== $aiPrompt->getPrompt()) {
                $text .= \sprintf('<p>AI Prompt: %s</p>', $aiPrompt->getPrompt());
            }

            if (null !== $aiPrompt->getResponseText()) {
                $text .= \sprintf('<p>Response: %s</p>', $aiPrompt->getResponseText());
            }

            return $text;
        }

        throw new \InvalidArgumentException('No text for embedding in page section');
    }

    public function getMetaAttributes(): array
    {
        return [
            'pageSection' => $this->getId(),

            // merge the meta attributes from the page; this connects the sections and the page information in one space where we can later search for it
            ...$this->pageTab->getPage()->getMetaAttributes(),
        ];
    }

    public function getAiPrompt(): ?PageSectionAIPrompt
    {
        return $this->aiPrompt;
    }

    public function setAiPrompt(PageSectionAIPrompt $aiPrompt): static
    {
        // set the owning side of the relation if necessary
        if ($aiPrompt->getPageSection() !== $this) {
            $aiPrompt->setPageSection($this);
        }

        $this->aiPrompt = $aiPrompt;

        return $this;
    }
}
