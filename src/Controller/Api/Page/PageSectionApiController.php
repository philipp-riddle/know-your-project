<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\PageSection;
use App\Entity\PageSectionAIPrompt;
use App\Entity\PageSectionEmbeddedPage;
use App\Entity\PageSectionSummary;
use App\Entity\PageSectionText;
use App\Entity\PageTab;
use App\Entity\Prompt;
use App\Form\Page\PageSectionForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\OrderListHandler;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use App\Service\Search\GenerationEngine;
use App\Service\Search\RecommendationEngine;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page/section')]
class PageSectionApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private GenerationEngine $generationEngine,
        private RecommendationEngine $recommendationEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    /**
     * When updating a page section we want to recommend similar pages to the user.
     * Not when retrieving, creating or deleting a page section.
     * 
     * @param PageSection $entity The entity to get additional data for.
     */
    protected function getAdditionalDataToSerialize(UserPermissionInterface $entity, string $httpMethod): array
    {
        if (\in_array($httpMethod, ['GET', 'POST', 'DELETE'], true)) {
            return [];
        }

        if (!($entity instanceof EntityVectorEmbeddingInterface)) {
            throw new \RuntimeException('The entity must implement the EntityVectorEmbeddingInterface to generate recommendations. Class: '.\get_class($entity));
        }

        return [
            // generate recommendations using the whole project as context
            // @todo add again later
            // 'recommendations' => $this->recommendationEngine->recommendSimilarContent(
            //     $this->getUser(),
            //     baseEntity: $entity,
            //     queryEntity: $entity->getPageTab()->getPage()->getProject(),
            //     excludeEntity: $entity->getPageTab()->getPage(),
            // ),
        ];
    }

    // === ROUTES ========
    // ====================
    
    #[Route('/{pageSection}', name: 'api_page_section_get', methods: ['GET'])]
    public function get(PageSection $pageSection): JsonResponse
    {
        return $this->crudGet($pageSection);
    }

    #[Route('/{pageSection}', name: 'api_page_section_delete', methods: ['DELETE'])]
    public function delete(PageSection $pageSection): JsonResponse
    {
        return $this->crudDelete($pageSection);
    }

    #[Route('/{pageSection}', name: 'api_page_section_update', methods: ['PUT'])]
    public function update(PageSection $pageSection, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            $pageSection,
            $request,
            onProcessEntity: function (PageSection $pageSection) {
                if (null !== $pageSection->getAiPrompt()) {
                    // generate the AI prompt response; this is done by chatting with the AI and using the page contents as context
                    $this->generationEngine->generatePageSectionAIPrompt($pageSection->getPageTab()->getPage(), $pageSection->getAiPrompt());
                }

                return $pageSection;
            },
        );
    }

    #[Route('', name: 'api_page_section_create', methods: ['POST'])]
    public function create(Request $request, OrderListHandler $orderListHandler): JsonResponse
    {
        return $this->crudUpdateOrCreateOrderListItem(
            null,
            $request,
            $orderListHandler,
            itemsToOrder: function (PageSection $pageSection) {
                if (null === $pageSection->getPageTab()) {
                    throw new BadRequestException('Section is not connected to a page tab.');
                }

                return $pageSection->getPageTab()->getPageSections();
            },
            onProcessEntity: function (PageSection $pageSection) use ($request) {
                $pageSection->setAuthor($this->getUser());
                $requestContent = $request->toArray();

                // edge case: when submitting a text section with '' as the string, it does resolve to NULL somehow. we need to make sure to set it to a valid PageSectionText entity.
                if ('' === @$requestContent['pageSectionText']['content']) {
                    $pageSectionText = (new PageSectionText())
                        ->setContent('');
                    $pageSection->setPageSectionText($pageSectionText);
                    $this->em->persist($pageSectionText);
                
                // edge case: empty page section embedded page
                } else  if (\array_key_exists('page', $requestContent['embeddedPage'] ?? []) && null === @$requestContent['embeddedPage']['page']) {
                    $pageSectionEmbeddedPage = (new PageSectionEmbeddedPage()) // initialise the embedded page with nothing
                        ->setPage(null);
                    $pageSection->setEmbeddedPage($pageSectionEmbeddedPage);
                    $this->em->persist($pageSectionEmbeddedPage);

                // edge case: empty AI prompt creation
                } else if ('' === @$requestContent['aiPrompt']['prompt']['promptText']) {
                    $prompt = (new Prompt())
                        ->setProject($pageSection->getPageTab()->getPage()->getProject())
                        ->setUser($this->getUser()) // log the current user - this can be used to further personalise the AI response for the user
                        ->setPromptText('')
                        ->setCreatedAt(new \DateTime());
                    $pageSectionAIPrompt = (new PageSectionAIPrompt())
                        ->setPrompt($prompt);
                    $pageSection->setAiPrompt($pageSectionAIPrompt);
                    $this->em->persist($pageSectionAIPrompt);

                // edge case: make sure to persist any child entities for the checklist as well
                } else if (null !== $pageSection->getPageSectionChecklist()) {
                    foreach ($pageSection->getPageSectionChecklist()->getPageSectionChecklistItems() as $item) {
                        $this->em->persist($item);
                    }
                // edge case: empty page section summary
                } else if ('' === @$requestContent['pageSectionSummary']['prompt']['promptText']) {
                    $pageSectionSummary = (new PageSectionSummary());
                    $pageSection->setPageSectionSummary($pageSectionSummary);

                    $this->em->persist($pageSectionSummary);
                }

                // == GENERATION ENGINE PART
                // If the block is connected to an AI prompt, generate the AI prompt response immediately if the prompt is not empty

                if (null !== $pageSection->getAiPrompt()) {
                    // generate the AI prompt response; this is done by chatting with the AI and using the page contents as context
                    $this->generationEngine->generatePageSectionAIPrompt($pageSection->getPageTab()->getPage(), $pageSection->getAiPrompt());
                } else if (null !== $pageSection->getPageSectionSummary()) {
                    // generate the summary for the page section
                    $this->generationEngine->generatePageSummary($this->getUser(), $pageSection->getPageTab()->getPage(), $pageSection->getPageSectionSummary());
                    $this->em->persist($pageSectionSummary->getPrompt());
                }

                return $pageSection;
            }
        );
    }

    #[Route('/order/{pageTab}', name: 'api_page_section_changeOrder', methods: ['PUT'])]
    public function changeOrder(PageTab $pageTab, Request $request, OrderListHandler $orderListHandler): JsonResponse
    {
        return $this->crudChangeOrder($request, $orderListHandler, \iterator_to_array($pageTab->getPageSections()));
    }

    public function getEntityClass(): string
    {
        return PageSection::class;
    }

    public function getFormClass(): string
    {
        return PageSectionForm::class;
    }
}