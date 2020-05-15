<?php


namespace Creonit\StorageBundle\Admin\StorageModule;


use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScope;
use Creonit\AdminBundle\Component\Scope\ListRowScopeRelation;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Creonit\StorageBundle\Model\StorageDataEntry;
use Creonit\StorageBundle\Model\StorageDataEntryQuery;
use Creonit\StorageBundle\Storage\Storage;
use Creonit\StorageBundle\Storage\StorageItem;
use Creonit\StorageBundle\Storage\StorageItemField;
use Symfony\Component\HttpFoundation\ParameterBag;

class StorageCollectionTable extends TableComponent
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var StorageItem
     */
    protected $storageItem;

    /**
     * @var StorageItemField|null
     */
    protected $captionItemField;

    /**
     * @title Коллекция элементов
     *
     * @field locale:select
     *
     * @event render(){
     *   this.node.find('form select').on('change', function(){
     *    $(this).closest('form').submit();
     *   });
     * }
     *
     * @header
     * {% if i18n %}
     *   <form class="form-inline pull-right">
     *    {{ locale | select | group }}
     *   </form>
     * {% endif %}
     *
     * {{ button('Добавить элемент', {size: 'sm', type: 'success', icon: icon}) | open('StorageEditor' , _query) }}
     *
     * @cols Название, .
     *
     * \StorageDataEntry
     * @entity Creonit\StorageBundle\Model\StorageDataEntry
     * @sortable true
     *
     *
     * @col {{ title | icon(_data.icon) | open('StorageEditor', _query | merge({key: _key})) | controls }}
     * @col {{ buttons(_visible() ~ _delete()) }}
     *
     */
    public function schema()
    {
        $this->storage = $this->container->get(Storage::class);

        $this->getField('locale')->setOptions(array_combine($this->storage->getLocales(), array_map('mb_strtoupper', $this->storage->getLocales())));
    }

    protected function loadData(ComponentRequest $request, ComponentResponse $response)
    {
        $this->storageItem = $this->storage->getItem($request->query->get('storage_item_name'));
        if (!$this->storageItem) {
            $response->flushError('Элемент не найден');
        }

        $this->captionItemField = $this->determineCaptionItemField($this->storageItem);

        $response->data->set('icon', $this->storageItem->getIcon() ?: 'cog');
        $response->data->set('i18n', $this->storageItem->isCollection() and $this->storageItem->isI18n());

        parent::loadData($request, $response);
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param StorageDataEntryQuery $query
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function filter(ComponentRequest $request, ComponentResponse $response, $query, Scope $scope, $relation, $relationValue, $level)
    {
        $query->filterByItemName($request->query->get('storage_item_name'));

        if ($locale = $request->query->get('locale')) {
            $query->filterByLocale($locale);

        } else {
            $query->filterByLocale('');
        }

        if ($context = $request->query->get('storage_entry_context')) {
            $query->filterByContext($context);

        } else {
            $query->filterByContext('');
        }
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param ParameterBag $data
     * @param StorageDataEntry $entity
     * @param Scope $scope
     * @param ListRowScopeRelation|null $relation
     * @param $relationValue
     * @param $level
     */
    protected function decorate(ComponentRequest $request, ComponentResponse $response, ParameterBag $data, $entity, Scope $scope, $relation, $relationValue, $level)
    {
        $title = $this->storageItem->getTitle() . ' #' . $entity->getId();

        if ($this->captionItemField) {
            $title = $this->storage->getDataFieldValue($this->captionItemField, $this->storage->retrieveDataField($entity, $this->captionItemField, $this->captionItemField->isI18n() ? 'ru' : null)) ?: $title;
        }

        $data->set('title', $title);
    }

    protected function determineCaptionItemField(StorageItem $storageItem)
    {
        foreach ($storageItem->getFields() as $itemField) {
            if ($itemField->isCaption()) {
                return $itemField;
            }
        }

        return $storageItem->getField('title');
    }
}