<?php


namespace Creonit\StorageBundle\Storage;


class StorageSection
{
    protected $path = '';
    protected $title = '';
    protected $icon = '';

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return StorageSection
     */
    public function setPath(string $path): StorageSection
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return StorageSection
     */
    public function setTitle(string $title): StorageSection
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return StorageSection
     */
    public function setIcon(string $icon): StorageSection
    {
        $this->icon = $icon;
        return $this;
    }
}