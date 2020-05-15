<?php

namespace Creonit\StorageBundle\Event;

use Creonit\AdminBundle\Component\Event\AfterComponentHandleEvent;
use Creonit\StorageBundle\Storage\Storage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Storage
     */
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setDefaultLocale', 0],
            ],
            AfterComponentHandleEvent::class => 'clearDataCache'
        ];
    }

    public function clearDataCache(AfterComponentHandleEvent $event)
    {
        if ($event->getResponse()->hasError()) {
            return;
        }

        if ($storageItemName = $event->getRequest()->query->get('storage_item_name')) {
            if ($storageItem = $this->storage->getItem($storageItemName)) {
                $this->storage->clearDataCache($storageItem, $event->getRequest()->query->get('storage_entry_context'));
            }
        }
    }

    public function setDefaultLocale(RequestEvent $event)
    {
        if (!$event->isMasterRequest() or $this->storage->getDefaultLocale() !== null) {
            return;
        }

        $this->storage->setDefaultLocale($event->getRequest()->getLocale());
    }
}