<?php

namespace App\Service\File;

use App\Entity\File;
use App\Exception\BadRequestException;
use Symfony\Component\HttpKernel\KernelInterface;

final class FileService
{
    public function __construct(
        private KernelInterface $kernel,
    ) { }

    public function getAbsolutePath(File $file): string
    {
        return \sprintf('%s%s', $this->kernel->getProjectDir(), $file->_getRelativePath());
    }

    /**
     * Delete the files from the file system.
     *
     * @param File[] $files
     */
    public function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            if (null === $file) { 
                continue;
            }

            if (!($file instanceof File)) {
                throw new BadRequestException('Expected an array of File entities, found '.\get_class($file));
            }

            $fileAbsolutePath = $this->getAbsolutePath($file);

            if (\file_exists($fileAbsolutePath)) {
                \unlink($fileAbsolutePath);
            }

            // remove the directory if it is empty
            $directory = \dirname($fileAbsolutePath);

            if (\is_dir($directory) && \count(\scandir($directory)) === 2) {
                \rmdir($directory);
            }
        }
    }
}