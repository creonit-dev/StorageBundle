<?php


namespace Creonit\StorageBundle\Storage;


class StorageItem
{
    protected $title = '';
    protected $name = '';
    protected $icon = '';
    protected $section = '';
    protected $collection = false;
    protected $context = false;
    protected $i18n = false;

    /**
     * @var array|StorageItemField[]
     */
    protected $fields = [];

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return StorageItem
     */
    public function setTitle(string $title): StorageItem
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return StorageItem
     */
    public function setName(string $name): StorageItem
    {
        $this->name = $name;
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
     * @return StorageItem
     */
    public function setIcon(string $icon): StorageItem
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection;
    }

    /**
     * @param bool $collection
     * @return StorageItem
     */
    public function setCollection(bool $collection): StorageItem
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return array|StorageItemField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array|StorageItemField[] $fields
     * @return StorageItem
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function getField($fieldName)
    {
        return $this->fields[$fieldName] ?? null;
    }

    /**
     * @return bool
     */
    public function isI18n(): bool
    {
        return $this->i18n;
    }

    /**
     * @param bool $i18n
     * @return StorageItem
     */
    public function setI18n(bool $i18n): StorageItem
    {
        $this->i18n = $i18n;
        return $this;
    }

    /**
     * @return string
     */
    public function getSection(): string
    {
        return $this->section;
    }

    /**
     * @param string $section
     * @return StorageItem
     */
    public function setSection(string $section): StorageItem
    {
        $this->section = $section;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContext(): bool
    {
        return $this->context;
    }

    /**
     * @param bool $context
     * @return StorageItem
     */
    public function setContext(bool $context): StorageItem
    {
        $this->context = $context;
        return $this;
    }
}