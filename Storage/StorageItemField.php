<?php


namespace Creonit\StorageBundle\Storage;


class StorageItemField
{
    protected $title = '';
    protected $name = '';
    protected $type = 'text';
    protected $notice = '';
    protected $i18n = false;
    protected $required = false;
    protected $caption = false;
    protected $default;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return StorageItemField
     */
    public function setTitle(string $title): StorageItemField
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
     * @return StorageItemField
     */
    public function setName(string $name): StorageItemField
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return StorageItemField
     */
    public function setType(string $type): StorageItemField
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return StorageItemField
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
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
     * @return StorageItemField
     */
    public function setI18n(bool $i18n): StorageItemField
    {
        $this->i18n = $i18n;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCaption(): bool
    {
        return $this->caption;
    }

    /**
     * @param bool $caption
     * @return StorageItemField
     */
    public function setCaption(bool $caption): StorageItemField
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotice(): string
    {
        return $this->notice;
    }

    /**
     * @param string $notice
     * @return StorageItemField
     */
    public function setNotice(string $notice): StorageItemField
    {
        $this->notice = $notice;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return StorageItemField
     */
    public function setRequired(bool $required): StorageItemField
    {
        $this->required = $required;
        return $this;
    }
}