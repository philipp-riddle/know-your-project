<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\Page\Page;
use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionText;
use App\Entity\Page\PageTab;
use App\Entity\Project\Project;
use App\Form\Page\PageForm;
use App\Repository\PageRepository;
use App\Repository\TaskRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\OrderListHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page')]
class PageApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private PageRepository $pageRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/user-list', name: 'api_page_userList', methods: ['GET'])]
    public function userList(): JsonResponse
    {
        return $this->crudList(['user' => $this->getUser(), 'project' => null]);
    }

    #[Route('/project-list/{project}', name: 'api_page_projectList', methods: ['GET'])]
    public function projectList(Project $project, Request $request): JsonResponse
    {
        $this->checkUserAccess($project);
        $tags = null;

        if ($request->query->get('tags') === '[]') {
            $tags = [];
        } else if ($request->query->get('tags') !== '') {
            $tags = \explode(',', $request->query->get('tags'));
        }

        $projectPages = $this->pageRepository->findProjectPages(
            $project,
            $request->query->get('query'),
            $request->query->get('limit'),
            \intval($request->query->get('excludeId', '')),
            $tags,
        );

        return $this->jsonSerialize($projectPages);
    }

    #[Route('/{page}', name: 'api_page_get', methods: ['GET'])]
    public function get(Page $page): JsonResponse
    {
        return $this->crudGet($page);
    }

    #[Route('/{page}', name: 'api_page_delete', methods: ['DELETE'])]
    public function delete(Page $page, TaskRepository $taskRepository): JsonResponse
    {
        return $this->crudDelete(
            $page,
            onProcessEntity: function (Page $page) use ($taskRepository) {
                if (null !== $taskPage = $taskRepository->findOneBy(['page' => $page])) {
                    $this->em->remove($taskPage); // remove the connected task if there is one
                }
            }
        );
    }

    #[Route('', name: 'api_page_create', methods: ['POST'])]
    public function create(Request $request, OrderListHandler $orderListHandler): JsonResponse
    {
        return $this->crudUpdateOrCreateOrderListItem(
            null,
            $request,
            $orderListHandler,
            itemsToOrder: function (Page $page) {
                // find all untagged pages; the created page is added to the end of the ordered list.
                return $this->pageRepository->findProjectPages(
                    $page->getProject(),
                    tags: [],
                );
            },
            onProcessEntity: function (Page $page) {
                $page->setUser($this->getUser()); // always set the user to know the author

                // we must initialize the page tab with some information to start
                $pageTab = (new PageTab())
                    ->setName('Tab 1')
                    ->setEmojiIcon('📝')
                    ->setCreatedAt(new \DateTime());
                $pageSection = (new PageSection())
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setAuthor($this->getUser())
                    ->setOrderIndex(0);
                $pageSectionText = (new PageSectionText())
                    ->setContent(''); // add empty content for the user to start
                $pageSection->setPageSectionText($pageSectionText);
                $pageTab->addPageSection($pageSection);
                $page->addPageTab($pageTab);

                $this->em->persist($pageTab);
                $this->em->persist($pageSection);
                $this->em->persist($pageSectionText);

                return $page;
            }
        );
    }

    #[Route('/{page}', name: 'api_page_update', methods: ['PUT'])]
    public function update(Page $page, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate($page, $request);
    }

    public function getEntityClass(): string
    {
        return Page::class;
    }

    public function getFormClass(): string
    {
        return PageForm::class;
    }
}