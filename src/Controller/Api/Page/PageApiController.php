<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\Page;
use App\Entity\PageTab;
use App\Entity\Project;
use App\Entity\User;
use App\Form\PageForm;
use App\Repository\PageRepository;
use App\Service\Helper\ApiControllerHelperService;
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
    public function projectList(Project $project): JsonResponse
    {
        $this->checkUserAccess($project);
        $projectPages = $this->pageRepository->findProjectPages($this->getUser(), $project);

        return $this->jsonSerialize($projectPages);
    }

    #[Route('/{page}', name: 'api_page_get', methods: ['GET'])]
    public function get(Page $page): JsonResponse
    {
        return $this->crudGet($page);
    }

    #[Route('/{page}', name: 'api_page_delete', methods: ['DELETE'])]
    public function delete(Page $page): JsonResponse
    {

        return $this->crudDelete($page);
    }

    #[Route('', name: 'api_page_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (Page $page) {
                if (null === $page->getProject()) {
                    $page->setUser($this->getUser());
                }

                $pageTab = (new PageTab())
                    ->setName('Tab 1')
                    ->setEmojiIcon('📝')
                    ->setCreatedAt(new \DateTime());
                $page->addPageTab($pageTab);
                $this->em->persist($pageTab);

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