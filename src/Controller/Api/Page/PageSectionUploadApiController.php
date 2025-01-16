<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\PageSection;
use App\Entity\PageSectionUpload;
use App\Entity\PageTab;
use App\Event\CreateCrudEntityEvent;
use App\Form\PageSectionForm;
use App\Repository\PageTabRepository;
use App\Service\File\Uploader;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\OrderListHandler;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page/section/upload')]
class PageSectionUploadApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private PageTabRepository $pageTabRepository,
        private OrderListHandler $orderListHandler,
        private Uploader $uploader,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    /**
     * We have to deal with this separately because we're uploading a file; not with a regular form.
     * Somehow the form did not want to submit properly although the data was there.
     */
    #[Route('', name: 'api_page_section_upload_create', methods: ['POST'])]
    public function createUpload(Request $request): JsonResponse
    {
        $payload = $request->request->all();

        if (null === ($pageTabId = $payload['pageTab'] ?? null)) {
            throw new BadRequestException('Invalid payload; please provide the page tab ID and the files array as a FormDataPart');
        }

        /** @var ?PageTab */
        $pageTab = $this->pageTabRepository->find($pageTabId);

        if (null === $pageTab) {
            throw new NotFoundHttpException('Invalid page tab ID');
        }

        // make sure the user has access to the page tab
        $this->checkUserAccess($pageTab);

        // we have to check if the files are uploaded properly first; get all of the files from the request
        $files = $request->files->all();

        if (\array_keys($files) !== ['files'] || !\is_array($files['files'])) {
            throw new BadRequestException('Invalid file upload; please upload one or multiple file(s) in the "files" field');
        }

        // iterate over all the uploaded files and create a page section for each
        $createdPageSectionUploads = [];

        foreach ($files['files'] as $file) {
            $createdPageSectionUploads[] = $pageSection = $this->handlePageSectionUpload($pageTab, $file);
            $this->em->flush();

            // we have to dispatch the event after the page section is persisted and flushed
            $this->eventDispatcher->dispatch(new CreateCrudEntityEvent($pageSection));
            $this->em->persist($pageSection);
        }

        $this->em->flush();

        return $this->jsonSerialize($createdPageSectionUploads);
    }

    /**
     * Handle the page section upload.
     * Converts a Symfony uploaded file to a file entity and assigns it to a page section.
     * 
     * @param PageTab $pageTab The page tab to which the page section should belong to
     * @param UploadedFile $file The uploaded file
     * 
     * @return PageSection The created page section with the included file
     */
    public function handlePageSectionUpload(PageTab $pageTab, UploadedFile $uploadedFile): PageSection
    {
        // this processes the file, uploads it and returns it as an entity we can assign to the page section
        $file = $this->uploader->upload(
            $this->getUser(),
            $pageTab->getPage()->getProject(),
            $uploadedFile,
        );

        $pageSectionUpload = (new PageSectionUpload())
            ->setFile($file);

        $pageSection = (new PageSection())
            ->initialize()
            ->setPageSectionUpload($pageSectionUpload)
            ->setAuthor($this->getUser());
        $pageTab->addPageSection($pageSection);

        $itemsToOrder = \iterator_to_array($pageSection->getPageTab()->getPageSections());
        $this->orderListHandler->add($pageSection, $itemsToOrder);
        
        // we have to persist the file, the page section upload first and its related entity, PageSection
        $this->em->persist($file);
        $this->em->persist($pageSectionUpload);
        $this->em->persist($pageSection);

        return $pageSection;
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