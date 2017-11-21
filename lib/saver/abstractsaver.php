<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 11:20
 */

namespace Fgsoft\Nmarket\Saver;


use Bitrix\Main\Diag\Debug;
use Fgsoft\Nmarket\Cache\Cache;
use Fgsoft\Nmarket\Cache\Memcache;
use Fgsoft\Nmarket\ExternalId\ExternalId;
use Fgsoft\Nmarket\Log\Log;
use Fgsoft\Nmarket\Log\Logger;
use Fgsoft\Nmarket\Node\Node;
use Fgsoft\Nmarket\Node\OfferNode;

abstract class AbstractSaver implements Saver
{
    /**
     * @var Node|OfferNode
     */
    protected $node;

    /**
     * Ключ по которому можно взять из node значение
     * применительно для однообразных справочников
     * @var string
     */
    protected $nodeKey;

    /**
     * @var ExternalId
     */
    protected $externalId;

    /**
     * Содержит поля и свойства сохраняемого элемента в формате:
     * [
     * 'NAME' => '',
     * 'DETAIL_TEXT' => '',
     * 'CODE' => '',
     * 'XML_ID' => '',
     *
     *  'PROPERTY_VALUES' => [KEY => VALUE]
     *
     * @var array
     */
    protected $fields;

    protected $iblockId;

    /**
     * @var Cache|Memcache
     */
    protected $cache;

    /**
     * AbstractSaver constructor.
     * @param Node $node
     * @param ExternalId $externalId
     * @param $iblockId
     * @param string $nodeKey
     * @param null|Cache|Memcache $cache
     */
    public function __construct(Node $node, ExternalId $externalId, $iblockId, $nodeKey = '', $cache = null)
    {
        $this->node = $node;
        $this->nodeKey = $nodeKey;
        $this->externalId = $externalId;
        $this->iblockId = $iblockId;

        if ($cache instanceof Cache) {
            $this->cache = $cache;
        }
    }

    /**
     * Заполнение массива fields из node
     * @return mixed
     */
    abstract function fillFields();

    public function save()
    {
        //1. проверяем необходимость загрузки элемента
        if (!$this->isNeedSave()) {
            return;
        }

        //2. Готовим поля и свойства элемента
        $this->fillFields();

        if (($element = $this->getElement())) {
            if (empty($element['PROPERTY_UPDATED_NOW_VALUE']) || $element['PROPERTY_UPDATED_NOW_VALUE'] == 'N') {
                \CIBlockElement::SetPropertyValuesEx(
                    $element['ID'],
                    $element['IBLOCK_ID'],
                    $this->fields['PROPERTY_VALUES']
                );

                $element['PROPERTY_UPDATED_NOW_VALUE'] = 'Y';
                $this->setToCache($this->externalId->get(), $element);
            }
        } else {
            $this->prepareFields();

            $ciblockelement = new \CIBlockElement();
            if (!$ciblockelement->Add($this->fields)) {
                Logger::getInstance()->add(
                    new Log(
                        'ERROR_SAVE',
                        $ciblockelement->LAST_ERROR . '(NODEKEY= ' . $this->nodeKey . ', INTERNAL_ID=' . $this->node->getInternalID() . ')\n\r'
                    )
                );
            }
        }
    }

    public function prepareFields()
    {
        if (!empty($this->fields['DETAIL_PICTURE']) && !is_array($this->fields['DETAIL_PICTURE'])) {
            $this->fields['DETAIL_PICTURE'] = \CFile::MakeFileArray($this->fields['DETAIL_PICTURE']);
            $this->setFileExtension($this->fields['DETAIL_PICTURE']);
        }
        if (!empty($this->fields['PREVIEW_PICTURE']) && !is_array($this->fields['PREVIEW_PICTURE'])) {
            $this->fields['PREVIEW_PICTURE'] = \CFile::MakeFileArray($this->fields['PREVIEW_PICTURE']);
            $this->setFileExtension($this->fields['PREVIEW_PICTURE']);
        }
    }

    protected function setFileExtension(&$fileArray)
    {
        if (!empty($fileArray['tmp_name']) && !empty($fileArray['type'])) {
            $fileArray['name'] = $fileArray['name'] . '.' . str_replace('image/', '', $fileArray['type']);
        }
    }

    public function getElement()
    {
        $result = [];

        if (!($result = $this->getFromCache($this->externalId->get()))) {
            $result = \CIBlockElement::GetList(
                [],
                ['=XML_ID' => $this->externalId->get()],
                false,
                ['nTopCount' => 1],
                [
                    'ID', 'IBLOCK_ID', 'XML_ID', 'ACTIVE', 'PROPERTY_UPDATED_NOW'
                ]
            )->Fetch();

           //Устанавливаем кэш после обновления элемента
        }

        return $result;
    }

    public function addField($name, $value)
    {
        $this->fields[$name] = $value;
    }

    public function addProperty($name, $value)
    {
        //Не планируется добавлять множественные поля
        $this->fields['PROPERTY_VALUES'][$name] = $value;
    }

    public function getByExternalId(ExternalId $externalId)
    {
        $result = [];

        if (!($result = $this->getFromCache($externalId->get()))) {
            $result = \Bitrix\Iblock\ElementTable::getList([
                'select' => ['ID', 'ACTIVE', 'XML_ID'],
                'filter' => ['=XML_ID' => $externalId->get()],
                'limit' => 1
            ])->fetch();

            $this->setToCache($externalId->get(), $result);
        }

        return $result;
    }

    /**
     * [
     *  'BUILDING' => BuildingExternalId,
     * 'FLOOR' => FloorExternalId.
     * ]
     * @param array $externalsId
     * @return bool
     */
    public function getPropertyValuesByExternals(array $externalsId)
    {
        if (empty($externalsId)) {
            return false;
        }

        //1. get xml_id from externals
        $result = [];
        foreach ($externalsId as $propertyName => $externalId) {
            $element = $this->getByExternalId($externalId);
            $result[$externalId->get()] = [
                'XML_ID' => $externalId->get(),
                'PROPERTY_CODE' => $propertyName,
                'ID' => $element['ID'],
                'ACTIVE' => $element['ACTIVE'],
            ];
        }

        return $result;
    }

    /**
     * Признак необходимости сохранения записи
     * К примеру: если ЖК не активно, то не нужно сохранять ни квартиры, ни его корпус
     *            если Корпус не активен, то не нужно сохранять квартиры
     *
     * @return bool
     */
    public function isNeedSave()
    {
        return true;
    }

    protected function getFromCache($key)
    {
        if (null == $this->cache) {
            return false;
        }

        return $this->cache->get($key);
    }

    protected function setToCache($key, $value)
    {
        if (null == $this->cache) {
            return false;
        }

        return $this->cache->set($key, $value);
    }
}