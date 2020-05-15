<?php


namespace Creonit\StorageBundle\Storage;


class StorageVariable implements \ArrayAccess
{
    /**
     * @var Storage
     */
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetGet($offset)
    {
        return $this->storage->get($offset, $this->storage->getDefaultLocale(), null, true);
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}