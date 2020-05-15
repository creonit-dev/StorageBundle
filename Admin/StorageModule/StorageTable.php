<?php


namespace Creonit\StorageBundle\Admin\StorageModule;


use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScope;
use Creonit\AdminBundle\Component\TableComponent;
use Creonit\StorageBundle\Storage\Storage;

class StorageTable extends TableComponent
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @title Управление контентом
     *
     * \StorageSection
     * @data []
     * @relation relation > StorageSection._key
     * @col <strong>{{ title | icon(icon) | controls }}</strong>
     *
     * \StorageItem
     * @relation section > StorageSection._key
     * @independent true
     * @data []
     * @col
     *
     * {% filter controls %}
     *   {{ title | icon(icon) | open(collection ? 'StorageCollectionTable' : 'StorageEditor', _query|merge({storage_item_name: _key, locale: locale})) }}
     *   {% if locales|length %}
     *     {% for locale in locales %}
     *       {{ ('<div class="label label-default" style="display: inline-block; vertical-align: middle">' ~ locale|upper ~ '</div>')| raw | open(collection ? 'StorageCollectionTable' : 'StorageEditor', _query|merge({storage_item_name: _key, locale: locale})) }}
     *     {% endfor %}
     *   {% endif %}
     * {% endfilter %}
     */
    public function schema()
    {
        $this->storage = $this->container->get(Storage::class);
    }

    protected function loadData(ComponentRequest $request, ComponentResponse $response)
    {
        $this->fixStorageItemSection();

        parent::loadData($request, $response);
    }

    protected function load(ComponentRequest $request, ComponentResponse $response, ListRowScope $scope, $relation = null, $relationValue = null, $level = 0)
    {
        $context = $request->query->get('storage_entry_context');
        $limitedItems = $request->query->get('storage_items', []);

        if ($scope->getName() === 'StorageItem') {
            $items = [];

            foreach ($this->storage->getItems() as $item) {
                if ($context) {
                    if (!$item->isContext()) {
                        continue;
                    }

                    if ($limitedItems and !in_array($item->getName(), $limitedItems)) {
                        continue;
                    }

                } else {
                    if ($item->getSection() != $relationValue) {
                        continue;
                    }

                    if ($item->isContext()) {
                        continue;
                    }
                }

                $itemData = [
                    '_key' => $item->getName(),
                    'title' => $item->getTitle(),
                    'section' => $item->getSection(),
                    'icon' => $item->getIcon() ?: ($item->isCollection() ? 'bars' : 'cog'),
                    'collection' => $item->isCollection(),
                    'locales' => ($item->isCollection() and $item->isI18n()) ? $this->storage->getLocales() : [],
                    'locale' => ($item->isCollection() and $item->isI18n()) ? $this->storage->getLocales()[0] : ''
                ];

                $items[] = $itemData;
            }

            return $items;

        } else if ($scope->getName() === 'StorageSection') {
            if ($context) {
                return [];
            }

            $sections = [];

            foreach ($this->storage->getSections() as $section) {
                if (dirname('/' . $section->getPath()) !== ('/' . $relationValue)) {
                    continue;
                }

                $sections[] = [
                    '_key' => $section->getPath(),
                    'title' => $section->getTitle(),
                    'icon' => $section->getIcon() ?: 'folder-o',
                    'relation' => $relationValue
                ];
            }

            return $sections;
        }

        return parent::load($request, $response, $scope, $relation, $relationValue, $level);
    }

    protected function fixStorageItemSection()
    {
        foreach ($this->storage->getItems() as $storageItem) {
            if (!$this->storage->hasSection($storageItem->getSection())) {
                $storageItem->setSection('');
            }
        }
    }
}