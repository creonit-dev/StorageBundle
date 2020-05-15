<?php


namespace Creonit\StorageBundle\Storage;


use Creonit\MediaBundle\Model\FileQuery;
use Creonit\MediaBundle\Model\ImageQuery;
use Creonit\StorageBundle\Model\StorageDataEntry;
use Creonit\StorageBundle\Model\StorageDataEntryQuery;
use Creonit\StorageBundle\Model\StorageDataField;
use Creonit\StorageBundle\Model\StorageDataFieldQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class Storage
{
    protected $defaultLocale;
    protected $locales = [];
    protected $sections = [];

    /** @var array|StorageItem[] */
    protected $items = [];

    /**
     * @var CacheInterface|AbstractAdapter
     */
    protected $cache;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    public function __construct(CacheInterface $cache, NormalizerInterface $normalizer)
    {
        $this->cache = $cache;
        $this->normalizer = $normalizer;
    }

    /**
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param mixed $defaultLocale
     * @return Storage
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
        return $this;
    }

    /**
     * @return array
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @param array $locales
     * @return Storage
     */
    public function setLocales(array $locales): Storage
    {
        $this->locales = $locales;
        return $this;
    }

    /**
     * @return StorageSection[]| array
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param array $sections
     * @return Storage
     */
    public function setSections(array $sections): Storage
    {
        $this->sections = $sections;

        foreach ($this->sections as $sectionPath => $section) {
            if ($section instanceof StorageSection) {
                continue;
            }

            $storageSection = new StorageSection();
            $storageSection->setPath($sectionPath);
            $storageSection->setTitle($section['title']);
            $storageSection->setIcon($section['icon']);

            $this->sections[$sectionPath] = $storageSection;
        }

        return $this;
    }

    /**
     * @param $sectionPath
     * @return StorageSection|mixed|null
     */
    public function getSection($sectionPath)
    {
        return $this->sections[$sectionPath] ?? null;
    }

    public function hasSection($sectionPath)
    {
        return isset($this->sections[$sectionPath]);
    }

    /**
     * @return array|StorageItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return Storage
     */
    public function setItems(array $items): Storage
    {
        $this->items = $items;

        foreach ($this->items as $itemName => $item) {
            if ($item instanceof StorageItem) {
                continue;
            }

            $storageItem = new StorageItem();
            $storageItem->setName($itemName);
            $storageItem->setTitle($item['title']);
            $storageItem->setIcon($item['icon']);
            $storageItem->setCollection($item['collection']);
            $storageItem->setContext($item['context']);
            $storageItem->setI18n($item['i18n']);
            $storageItem->setSection($item['section']);

            foreach ($item['fields'] as $fieldName => $field) {
                if ($field instanceof StorageItemField) {
                    continue;
                }

                $storageItemField = new StorageItemField();
                $storageItemField->setName($fieldName);
                $storageItemField->setType($field['type']);
                $storageItemField->setTitle($field['title']);
                $storageItemField->setDefault($field['default']);
                $storageItemField->setI18n($field['i18n']);
                $storageItemField->setRequired($field['required']);
                $storageItemField->setCaption($field['caption']);
                $storageItemField->setNotice($field['notice']);

                $item['fields'][$fieldName] = $storageItemField;
            }

            $storageItem->setFields($item['fields']);

            $this->items[$itemName] = $storageItem;
        }

        return $this;
    }

    /**
     * @param $itemName
     * @return StorageItem|mixed|null
     */
    public function getItem($itemName)
    {
        return $this->items[$itemName] ?? null;
    }

    public function getDataEntryById($id)
    {
        return StorageDataEntryQuery::create()->findPk($id);
    }

    protected function getDataEntryQuery(StorageItem $item, $locale = null, $context = null)
    {
        $query = StorageDataEntryQuery::create()
            ->filterByItemName($item->getName())
            ->filterByVisible(true);

        if ($item->isCollection() and $item->isI18n()) {
            if (!$locale) {
                throw new \RuntimeException(sprintf('Чтобы получить запись из коллекции «%s» требуется указать локаль, так как включен режим i18n', $item->getName()));
            }

            $query->filterByLocale($locale);

        } else {
            $query->filterByLocale('');
        }

        if ($item->isContext()) {
            if (!$context) {
                throw new \RuntimeException(sprintf('Чтобы получить запись из «%s» требуется указать контекст', $item->getName()));
            }

            $query->filterByContext($context);

        } else {
            $query->filterByContext('');
        }

        return $query;
    }

    public function getDataEntry(StorageItem $item, $locale = null, $context = null)
    {
        return $this->getDataEntryQuery($item, $locale, $context)->orderByCreatedAt(Criteria::DESC)->findOne();
    }

    public function getDataEntries(StorageItem $item, $locale = null, $context = null)
    {
        return $this->getDataEntryQuery($item, $locale, $context)->orderBySortableRank()->find();
    }

    public function retrieveDataEntry(StorageItem $item, $locale = null, $context = null)
    {
        if ($item->isCollection()) {
            return $this->createDataEntry($item, $locale, $context);
        }

        return $this->getDataEntry($item, $locale, $context) ?: $this->createDataEntry($item, $locale, $context);
    }

    public function createDataEntry(StorageItem $item, $locale = null, $context = null)
    {
        $dataEntry = new StorageDataEntry();
        $dataEntry->setItemName($item->getName());

        if ($item->isCollection() and $item->isI18n()) {
            if (!$locale) {
                throw new \RuntimeException(sprintf('Чтобы создать запись для коллекции «%s» требуется указать локаль, так как включен режим i18n', $item->getName()));
            }

            $dataEntry->setLocale($locale);
        }

        if ($item->isContext()) {
            if (!$context) {
                throw new \RuntimeException(sprintf('Чтобы создать запись для «%s» требуется указать контекст', $item->getName()));
            }

            $dataEntry->setContext($context);
        }

        return $dataEntry;
    }

    public function getDataField(StorageDataEntry $dataEntry, StorageItemField $itemField, $locale = null)
    {
        if ($dataEntry->isNew()) {
            return null;
        }

        return StorageDataFieldQuery::create()
            ->filterByLocale($locale ?: '')
            ->filterByFieldName($itemField->getName())
            ->filterByStorageDataEntry($dataEntry)
            ->orderByCreatedAt(Criteria::DESC)
            ->findOne();
    }

    public function retrieveDataField(StorageDataEntry $dataEntry, StorageItemField $itemField, $locale = null)
    {
        return $this->getDataField($dataEntry, $itemField, $locale) ?: $this->createDataField($dataEntry, $itemField, $locale);
    }

    public function createDataField(StorageDataEntry $dataEntry, StorageItemField $itemField, $locale = null)
    {
        $dataField = new StorageDataField();
        $dataField->setFieldName($itemField->getName());
        $dataField->setLocale($locale ?: '');
        $dataField->setStorageDataEntry($dataEntry);

        if (null !== $defaultValue = $this->getDefaultDataFieldValue($itemField, $dataField)) {
            $dataField->setValue($defaultValue);
        }

        return $dataField;
    }

    public function getDataFieldValue(StorageItemField $itemField, StorageDataField $dataField)
    {
        switch ($itemField->getType()) {
            case 'file':
                return $dataField->getValue() ? FileQuery::create()->findPk($dataField->getValue()) : null;

            case 'image':
                return $dataField->getValue() ? ImageQuery::create()->findPk($dataField->getValue()) : null;

            case 'checkbox':
                return (bool)$dataField->getValue();
        }

        return $dataField->getValue();
    }

    public function getDefaultDataFieldValue(StorageItemField $itemField, StorageDataField $dataField)
    {
        if ($defaultValue = $itemField->getDefault()) {
            if (is_array($defaultValue)) {
                if ($dataField->getLocale() and isset($defaultValue[$dataField->getLocale()])) {
                    return $defaultValue[$dataField->getLocale()];
                }

            } else {
                return $defaultValue;
            }
        }

        return null;
    }

    public function getDataEntryValues(StorageItem $item, StorageDataEntry $dataEntry, $locale = null)
    {
        $values = [];
        foreach ($item->getFields() as $itemField) {
            $values[$itemField->getName()] = $this->getDataFieldValue(
                $itemField,
                $this->retrieveDataField(
                    $dataEntry,
                    $itemField,
                    $itemField->isI18n() ? $locale : null
                )
            );
        }

        return $values;
    }

    public function getData(StorageItem $item, $locale = null, $context = null)
    {
        if ($item->isCollection()) {
            return array_map(
                function (StorageDataEntry $dataEntry) use ($item, $locale) {
                    return $this->getDataEntryValues($item, $dataEntry, $locale);
                },
                $this->getDataEntries($item, $locale, $context)->getData()
            );
        }

        return $this->getDataEntryValues($item, $this->retrieveDataEntry($item, $locale, $context), $locale);
    }


    public function getNormalizedData(StorageItem $item, $locale = null, $context = null)
    {
        return $this->normalizer->normalize($this->getData($item, $locale, $context));
    }

    public function getDataCacheKey(StorageItem $item, $locale = null, $context = null)
    {
        return str_replace(
            ['{', '}', '(', ')', '/', '\\', '@', ':',],
            '_',
            implode('__', ['storage', urlencode($item->getName()), $locale ?: 'none', $context ?: 'none'])
        );
    }

    public function getNormalizedDataCacheKey(StorageItem $item, $locale = null, $context = null)
    {
        return $this->getDataCacheKey($item, $locale, $context) . '__normalized';
    }

    public function getCacheableData(StorageItem $item, $locale = null, $context = null)
    {
        return $this->cacheData($this->getDataCacheKey($item, $locale, $context), function () use ($item, $locale, $context) {
            return $this->getData($item, $locale, $context);
        });
    }

    public function getCacheableNormalizedData(StorageItem $item, $locale = null, $context = null)
    {
        return $this->cacheData($this->getNormalizedDataCacheKey($item, $locale, $context), function () use ($item, $locale, $context) {
            return $this->getNormalizedData($item, $locale, $context);
        });
    }

    protected function cacheData(string $key, callable $callable)
    {
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->expiresAfter(3600 * 24);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $data = $callable();
        $cacheItem->set($data);
        $this->cache->save($cacheItem);

        return $data;
    }

    public function clearDataCache(StorageItem $item, $context = null)
    {
        $this->cache->deleteItem($this->getDataCacheKey($item, $context));
        $this->cache->deleteItem($this->getNormalizedDataCacheKey($item, $context));

        foreach ($this->locales as $locale) {
            $this->cache->deleteItem($this->getDataCacheKey($item, $locale, $context));
            $this->cache->deleteItem($this->getNormalizedDataCacheKey($item, $locale, $context));
        }
    }

    public function get($query, $locale = null, $context = null, $normalize = false)
    {
        list($itemName, $fieldName) = $this->determineDataQueryNames($query);

        if (!$item = $this->getItem($itemName)) {
            return null;
        }

        if ($locale === null) {
            $locale = $this->getDefaultLocale();
        }

        if ($context !== null) {
            $context = $this->normalizeContext($context);
        }

        $data = $normalize ? $this->getCacheableNormalizedData($item, $locale, $context) : $this->getCacheableData($item, $locale, $context);

        if ($fieldName) {
            return $data[$fieldName] ?? null;
        }

        return $data;
    }

    protected function determineDataQueryNames($query)
    {
        if (preg_match('/^([a-z\d_-]+)\.([a-z\d_-]+)$/usi', $query, $match)) {
            return [$match[1], $match[2]];

        } else {
            return [$query, null];
        }
    }

    public function normalizeContext($context)
    {
        if (is_string($context)) {
            return $context;
        }

        if ($context instanceof ActiveRecordInterface) {
            if (method_exists($context, 'getPrimaryKey')) {
                $primaryKey = $context->getPrimaryKey();

                if (is_array($primaryKey)) {
                    $primaryKey = implode(',', $primaryKey);
                }

                return get_class($context) . ':' . $primaryKey;
            }
        }

        throw new \RuntimeException('Неподдерживаемый тип контекста');
    }
}