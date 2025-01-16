<?php

namespace App\Controller\Api\File;

use App\Controller\Api\ApiController;
use App\Entity\File;
use App\Service\File\FileService;
use App\Service\Helper\ApiControllerHelperService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/file')]
class FileApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private FileService $fileService,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/download/{file}', methods: ['GET'], name: 'api_file_download')]
    public function downloadFile(File $file): BinaryFileResponse
    {
        $this->checkUserAccess($file);

        $fileAbsolutePath = $this->fileService->getAbsolutePath($file);
        $exportExtension = \pathinfo($fileAbsolutePath, PATHINFO_EXTENSION);
        $fileName = $file->getName();

        if (!\str_contains($fileName, $exportExtension)) {
            $fileName .= '.'.$exportExtension;
        }

        $response = new BinaryFileResponse($fileAbsolutePath);
        $response->headers->set('Content-type', $file->getMimeType());
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('access-control-allow-origin', '*');

        return $response;
    }
}