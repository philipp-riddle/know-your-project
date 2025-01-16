<?php

namespace App\Service\File;

use App\Entity\File;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

final class Uploader
{
    public const DISALLOWED_MIMETYPES = ['application/octet-stream'];
    public const PUBLIC_RELATIVE_PATH = '/public/user/uploads'; // these can be directly accessed through the web
    public const PRIVATE_RELATIVE_PATH = '/user/uploads'; // these are stored in a private directory; for all other files besides images which are not directly displayed in the browser 

    public function __construct(
        private KernelInterface $kernel,
    ) { }

    public function upload(User $user, ?Project $project, UploadedFile $uploadedFile): File
    {
        if (\in_array($uploadedFile->getMimeType(), self::DISALLOWED_MIMETYPES, true)) {
            throw new BadRequestException('Uploaded file type is not allowed: '.$uploadedFile->getMimeType());
        }

        $mimeTypeParts = \explode('/', $uploadedFile->getMimeType() ?? '');
        $mimeTypeCategory = $mimeTypeParts[0] ?? null;
        
        if (null === $mimeTypeCategory) {
            throw new BadRequestException('Uploaded invalid file; could not read mime type');
        }

        $randomFileName = \bin2hex(\random_bytes(10));
        $randomFileDirectory = \substr($randomFileName, 0, 3);

        if ($mimeTypeCategory === 'image') {
            $relativePath = self::PUBLIC_RELATIVE_PATH;
        } else {
            $relativePath = self::PRIVATE_RELATIVE_PATH;
        }

        // e.g. /var/www/application/user/uploads/image/abc/def123.jpg
        $uploadDir = \sprintf('%s/%s/%s', $relativePath, $mimeTypeCategory, $randomFileDirectory);
        $absoluteUploadDir = \sprintf('%s%s', $this->kernel->getProjectDir(), $uploadDir);
        $uploadFileName = \sprintf('%s.%s', $randomFileName, $uploadedFile->guessExtension());
        $relativeUploadPath = \sprintf('%s/%s', $uploadDir, $uploadFileName);

        if (!\is_dir($absoluteUploadDir)) {
            \mkdir($absoluteUploadDir, recursive: true);
        }

        // get the file contents from the temporary file
        // somehow the Symfony UploadedFile won't read from our local /tmp properly, thus we read it here with native PHP file resource functions
        $fp = \fopen($uploadedFile->getRealPath(), 'r');
        $fileContent = \fread($fp, \filesize($uploadedFile->getRealPath()));
        \fclose($fp);
        // move the file into the correct directory
        \file_put_contents($absoluteUploadDir.'/'.$uploadFileName, $fileContent);

        // return a new entity with the file information; this can be used anywhere in the application to store user content
        return (new File())
            ->setUser($user)
            ->setProject($project)
            ->setMimeType($uploadedFile->getMimeType())
            ->setSize($uploadedFile->getSize() ? (int) $uploadedFile->getSize() : 0)
            ->setName($uploadedFile->getClientOriginalName())
            ->setRelativePath($relativeUploadPath)
            ->initialize()
        ;
    }
}