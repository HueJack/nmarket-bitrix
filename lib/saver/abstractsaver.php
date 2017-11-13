<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 11:20
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\ExternalId\ExternalId;
use Fgsoft\Nmarket\Node\Node;
use Bitrix\Main\Loader;
use Fgsoft\Nmarket\Node\OfferNode;

Loader::includeModule('iblock');

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

    public function __construct(Node $node, ExternalId $externalId, $iblockId, $nodeKey = '')
    {
        $this->node = $node;
        $this->nodeKey = $nodeKey;
        $this->externalId = $externalId;
        $this->iblockId = $iblockId;
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

        if ($element = $this->getElement() && empty($element['PROPERTY_DONT_NEED_UPDATE_VALUE'])) {
            \CIBlockElement::SetPropertyValuesEx(
                $element['ID'],
                $element['IBLOCK_ID'],
                $this->fields['PROPERTY_VALUES']
            );
        } else {
            $this->prepareFields();
            $ciblockelement = new \CIBlockElement();
            if (!$ciblockelement->Add($this->fields)) {
                echo 'Ошибка ' . $ciblockelement->LAST_ERROR . '<br>';
                print_r($this->fields);
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

//        if (!empty($this->fields['PROPERTIES'])) {
//            foreach ($this->fields['PROPERTIES'] as $propertyName => $value) {
//
//            }
//        }
    }

    protected function setFileExtension(&$fileArray)
    {
        if (!empty($fileArray['tmp_name']) && !empty($fileArray['type'])) {
            $fileArray['name'] = $fileArray['name'] . '.' . str_replace('image/', '', $fileArray['type']);
        }
    }

    public function getElement()
    {
        return \CIBlockElement::GetList(
            [],
            ['=XML_ID' => $this->externalId->get()],
            false,
            ['nTopCount' => 1],
            [
                'ID', 'IBLOCK_ID', 'XML_ID', 'PROPERTY_DONT_NEED_UPDATE'
            ]
        )->Fetch();
//        return \Bitrix\Iblock\ElementTable::getList([
//            'select' => ['ID', 'XML_ID', 'IBLOCK_ID'],
//            'filter' => ['=XML_ID' => $this->externalId->get()]
//        ])->fetch();
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

    public static function getByExternalId(ExternalId $externalId)
    {
        return \Bitrix\Iblock\ElementTable::getList([
            'select' => ['ID', 'ACTIVE'],
            'filter' => ['=XML_ID' => $externalId->get()],
            'limit' => 1
        ])->fetch();
    }

    /**
     * [
     *  'BUILDING' => BuildingExternalId,
     * 'FLOOR' => FloorExternalId.
     * ]
     * @param array $externalsId
     * @return bool
     */
    public static function getPropertyValuesByExternals(array $externalsId)
    {
        if (empty($externalsId)) {
            return false;
        }

        //1. get xml_id from externals
        $xmlIdExternals = [];
        foreach ($externalsId as $propertyName => $externalId) {
            $xmlIdExternals[$externalId->get()] = [
                'XML_ID' => $externalId->get(),
                'PROPERTY_CODE' => $propertyName
            ];
        }

        $result = [];
        if (!empty($xmlIdExternals)) {
            $rsElements = \Bitrix\Iblock\ElementTable::getList([
                'select' => ['ID', 'XML_ID', 'ACTIVE'],
                'filter' => ['XML_ID' => array_keys($xmlIdExternals)]
                ]
            );
            while ($item = $rsElements->fetch()) {
                $result[$item['XML_ID']] = [
                    'ID' => $item['ID'],
                    'ACTIVE' => $item['ACTIVE'],
                    'PROPERTY_CODE' => $xmlIdExternals[$item['XML_ID']]['PROPERTY_CODE'],
                    'XML_ID' => $item['XML_ID']
                ];
            }
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
    protected function isNeedSave()
    {
        return true;
    }
}