<?php

namespace App\EventSubscriber;

use App\Event\CreateCrudEntityEvent;
use App\Event\DeleteCrudEntityEvent;
use App\Event\UpdateCrudEntityEvent;
use App\Service\Integration\MercureEntityEvent;
use App\Service\Integration\MercureIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MercureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MercureIntegration $mercureIntegration,
    ) { }

    public function onCreate(CreateCrudEntityEvent $createEvent): void
    {
        $this->mercureIntegration->publishEntityEvent($createEvent->getEntity(), MercureEntityEvent::CREATE, $createEvent->getUser());
    }

    public function onUpdate(UpdateCrudEntityEvent $updateEvent): void
    {

        $this->mercureIntegration->publishEntityEvent($updateEvent->getEntity(), MercureEntityEvent::UPDATE, $updateEvent->getUser());
    }

    public function onDelete(DeleteCrudEntityEvent $deleteEvent): void
    {
        $this->mercureIntegration->publishEntityEvent($deleteEvent->getEntity(), MercureEntityEvent::DELETE, $deleteEvent->getUser());
    }

    public static function getSubscribedEvents()
    {
        return [
            CreateCrudEntityEvent::class => 'onCreate',
            UpdateCrudEntityEvent::class => 'onUpdate',
            DeleteCrudEntityEvent::class => 'onDelete',
        ];
    }
}