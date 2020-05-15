<?php


namespace Creonit\StorageBundle\Admin\StorageModule;


use Creonit\AdminBundle\Component\EditorComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\StorageBundle\Model\StorageDataEntry;
use Creonit\StorageBundle\Storage\Storage;
use Creonit\StorageBundle\Storage\StorageItem;
use Creonit\StorageBundle\Storage\StorageItemField;

class StorageEditor extends EditorComponent
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @event render(){
     *   this.node.find('.modal-title').text(this.data.title);
     * }
     *
     * @entity Creonit\StorageBundle\Model\StorageDataEntry
     * @template
     *
     * {% if i18n %}
     *   <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px">
     *     {% for locale in locales %}
     *       <li role="presentation" class="{{ not loop.index0 ? 'active' : '' }}"><a href="[data-field-locale={{ locale }}]" aria-controls="home" role="tab" data-toggle="tab">{{ locale|upper }}</a></li>
     *     {% endfor %}
     *   </ul>
     * {% endif %}
     *
     * <div class="tab-content">
     * {% for field in fields %}
     *   {% if field.locale %}
     *     <div role="tabpanel" class="tab-pane {{ field.locale == locales[0] ? 'active' : '' }}" data-field-locale="{{ field.locale }}">
     *   {% endif %}
     *
     *   {% if field.type == 'text' %}
     *     {{ field.value | text(field.name) | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'textarea' %}
     *     {{ field.value | textarea(field.name) | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'textedit' %}
     *     {{ field.value | textedit(field.name) | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'image' %}
     *     {{ field.value | image(field.name) | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'file' %}
     *     {{ field.value | file(field.name) | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'video' %}
     *     {{ field.value | video(field.name) | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'checkbox' %}
     *     {{ field.value | checkbox(field.name, field.title ~ '' ~ (field.notice ? '' ~ ('<span class="control-label-notice">' ~ field.notice ~ '</span>') : '')) | group }}
     *   {% elseif field.type == 'date' %}
     *     {{ field.value | input(field.name, 'date') | group(field.title, {notice: field.notice}) }}
     *   {% elseif field.type == 'datetime' %}
     *     {{ field.value | input(field.name, 'datetime') | group(field.title, {notice: field.notice}) }}
     *   {% endif %}
     *
     *   {% if field.locale %}
     *     </div>
     *   {% endif %}
     * {% endfor %}
     * </div>
     */
    public function schema()
    {
        $this->storage = $this->container->get(Storage::class);
    }

    protected function retrieveStorageDataEntry(StorageItem $storageItem, ComponentRequest $request, ComponentResponse $response)
    {
        if ($key = $request->query->get('key')) {
            if (!$entity = $this->storage->getDataEntryById($key)) {
                $response->flushError('Элемент не найден');
            }

            return $entity;
        }

        return $this->storage->retrieveDataEntry($storageItem, $request->query->get('locale'), $request->query->get('storage_entry_context'));
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @return StorageItem
     */
    protected function retrieveStorageItem(ComponentRequest $request, ComponentResponse $response)
    {
        if (!$storageItem = $this->storage->getItem($request->query->get('storage_item_name'))) {
            $response->flushError('Элемент не найден');
        }

        return $storageItem;
    }

    public function loadData(ComponentRequest $request, ComponentResponse $response)
    {
        $storageItem = $this->retrieveStorageItem($request, $response);
        $storageDataEntry = $this->retrieveStorageDataEntry($storageItem, $request, $response);

        $fields = [];
        foreach ($storageItem->getFields() as $field) {
            foreach ($this->getDataFields($storageDataEntry, $field) as $dataField) {
                $fields[] = [
                    'name' => $field->getName() . ($dataField->getLocale() ? ('__' . $dataField->getLocale()) : ''),
                    'title' => ($dataField->getLocale() ? sprintf('<div class="label label-default" style="display: inline-block; position: relative; top: -2px; margin-right: 5px;">%s</div>', strtoupper($dataField->getLocale())) : '') . $field->getTitle() . ($field->isRequired() ? '*' : ''),
                    'type' => $field->getType(),
                    'locale' => $dataField->getLocale(),
                    'notice' => $field->getNotice(),
                    'value' => $this->createField('value', [], $this->getComponentFieldType($field))->load($dataField),
                ];
            }
        }

        $response->data->set('fields', $fields);
        $response->data->set('title', $storageItem->getTitle());
        $response->data->set('locales', $this->storage->getLocales());
        $response->data->set('i18n', $this->isI18n($storageItem));
    }

    public function saveData(ComponentRequest $request, ComponentResponse $response)
    {
        $storageItem = $this->retrieveStorageItem($request, $response);
        $storageDataEntry = $this->retrieveStorageDataEntry($storageItem, $request, $response);

        $dataFields = [];
        foreach ($storageItem->getFields() as $field) {
            foreach ($this->getDataFields($storageDataEntry, $field) as $dataField) {
                $dataFields[] = $dataField;
                $fieldName = $field->getName() . ($dataField->getLocale() ? ('__' . $dataField->getLocale()) : '');
                $componentField = $this->createField($fieldName, [], $this->getComponentFieldType($field));
                $data = $componentField->extract($request);
                $componentField->setName('value');
                $componentField->save($dataField, $data);

                if ($field->isRequired() and !$data) {
                    $response->error('Требуется заполнить поле', $fieldName);
                }
            }
        }

        if($response->hasError()){
            $response->flushError();
        }

        $storageDataEntry->save();

        foreach ($dataFields as $dataField) {
            if ($dataField->isModified()) {
                $dataField->save();
            }
        }

        $request->query->set('key', $storageDataEntry->getId());
        $response->query->set('key', $storageDataEntry->getId());

        $response->sendSuccess();
    }

    protected function getComponentFieldType(StorageItemField $field)
    {
        switch ($field->getType()) {
            case 'image':
            case 'file':
            case 'video':
            case 'gallery':
            case 'checkbox':
                return $field->getType();
        }

        return 'default';
    }

    protected function getDataFields(StorageDataEntry $dataEntry, StorageItemField $itemField)
    {
        $dataFields = [];
        if ($itemField->isI18n()) {
            foreach ($this->storage->getLocales() as $locale) {
                $dataFields[] = $this->storage->retrieveDataField($dataEntry, $itemField, $locale);
            }

        } else {
            $dataFields[] = $this->storage->retrieveDataField($dataEntry, $itemField);
        }

        return $dataFields;
    }

    protected function isI18n(StorageItem $storageItem)
    {
        foreach ($storageItem->getFields() as $itemField) {
            if ($itemField->isI18n()) {
                return true;
            }
        }

        return false;
    }
}