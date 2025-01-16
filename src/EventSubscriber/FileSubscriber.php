<?php

namespace App\EventSubscriber;

use App\Event\DeleteCrudEntityEvent;
use App\Service\File\FileService;
use App\Service\File\Interface\EntityFileInterface;
use App\Service\File\Interface\EntityMultipleFileInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This subscriber is used to any events which are relevant for managing the files in the file system.
 */
class FileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FileService $FileService,
    ) { }

    /**
     * If an entity related to any page(s) is deleted we want to delete the images as well.
     */
    public function onDeleteCrudEntityEvent(DeleteCrudEntityEvent $event): void
    {
        $entity = $event->getEntity();

        // only handle the entity if it manages one or multiple files
        if (!($entity instanceof EntityFileInterface) && !($entity instanceof EntityMultipleFileInterface)) {
            return;
        }

        $filesToDelete = $entity instanceof EntityMultipleFileInterface ? $entity->getFiles() : [$entity->getFile()];
        $this->FileService->deleteFiles($filesToDelete);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeleteCrudEntityEvent ::class => 'onDeleteCrudEntityEvent',
        ];
    }
}