<?php


namespace Creonit\StorageBundle\Admin\StorageModule;


use Creonit\AdminBundle\Module;

class StorageModule extends Module
{
    public function initialize()
    {
        $this->addComponent(new StorageTable());
        $this->addComponent(new StorageEditor());
        $this->addComponent(new StorageCollectionTable());
    }
}