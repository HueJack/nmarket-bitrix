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

        $this->fillFields();
        $this->prepareFields();
    }

    /**
     * Заполнение массива fields из node
     * @return mixed
     */
    abstract function fillFields();

    public function save()
    {
        if ($element = $this->getElement()) {
            \CIBlockElement::SetPropertyValuesEx(
                $element['ID'],
                $element['IBLOCK_ID'],
                $this->fields['PROPERTY_VALUES']
            );
        } else {
            $ciblockelement = new \CIBlockElement();
            if (!$ciblockelement->Add($this->fields)) {
                print_r($this->fields);
                ShowError($ciblockelement->LAST_ERROR);
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
        return \Bitrix\Iblock\ElementTable::getList([
            'select' => ['ID', 'XML_ID', 'IBLOCK_ID'],
            'filter' => ['=XML_ID' => $this->externalId->get()]
        ])->fetch();
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
            'select' => ['ID'],
            'filter' => ['=XML_ID' => $externalId->get()],
            'limit' => 1
        ])->fetch();
    }
}