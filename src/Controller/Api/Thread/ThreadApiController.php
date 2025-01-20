<?php

namespace App\Controller\Api\Thread;

use App\Controller\Api\CrudApiController;
use App\Entity\PageSection;
use App\Entity\Thread;
use App\Entity\ThreadPageSectionContext;
use App\Form\Thread\ThreadForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/thread')]
class ThreadApiController extends CrudApiController
{
    #[Route('', methods: ['POST'], name: 'api_thread_create')]
    public function createThread(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (Thread $thread, FormInterface $form) {
                /** @var PageSection */
                $pageSection = $form->get('pageSection')->getData();

                // the only thing we need to here is to create the page section context
                $pageSectionContext = (new ThreadPageSectionContext())
                    ->setPageSection($pageSection);
                $thread->setPageSectionContext($pageSectionContext);
                $thread->setProject($pageSection->getPageTab()->getPage()->getProject());
                $this->em->persist($pageSectionContext);

                return $thread;
            },
        );
    }

    public function getEntityClass(): string
    {
        return Thread::class;
    }

    public function getFormClass(): string
    {
        return ThreadForm::class;
    }
}