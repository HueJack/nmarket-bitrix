<?php
/**
 * Фасад для формирования всей логики работы с объектами
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:53
 */

namespace Fgsoft\Nmarket\Facade;

use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
use Fgsoft\Nmarket\Cache\Cache;
use Fgsoft\Nmarket\Cache\Memcache;
use Fgsoft\Nmarket\Fabric\FabricExternalId;
use Fgsoft\Nmarket\Log\Log;
use Fgsoft\Nmarket\Log\Logger;
use Fgsoft\Nmarket\Saver\BuildingSaver;
use Fgsoft\Nmarket\Saver\ComplexSaver;
use Fgsoft\Nmarket\Saver\DictionarySaver;
use Fgsoft\Nmarket\Saver\FlatSaver;
use Fgsoft\Nmarket\Saver\FloorSaver;
use \Bitrix\Main\Loader;
use Fgsoft\Nmarket\Saver\PictureSave;

class FacadeProcessing
{
    /**
     * @var Logger
     */
    public static $logger;

    /**
     * Запуск процесса выгрузки
     *
     * Logger передается инстанцированый Logger::getInstance();
     * @param \XMLReader $xmlReader
     * @param bool $downloadPictures
     * @param Logger $logger
     */
    public static function process(\XMLReader $xmlReader, $downloadPictures = false, Logger $logger)
    {
        $fields = [];
        $currentInternalId = 0;
        $currentNode = '';
        $index = 0;

        self::$logger = $logger;

        Loader::includeModule('iblock');

        if (!$downloadPictures) {
            self::beforeProcess();
        }

        $memcache =  new Memcache('localhost', 11211);

        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == \XMLReader::ELEMENT && $xmlReader->localName == 'offer') {
                $currentInternalId = $xmlReader->getAttribute('internal-id');
                continue;
            }

            if (0 == $currentInternalId) {
                continue;
            }

            if ($xmlReader->nodeType == \XMLReader::ELEMENT) {
                $currentNode = $xmlReader->localName;
                $fields[$currentInternalId]['fields'][$xmlReader->localName] = [];

                if (in_array($xmlReader->localName, ['price', 'area', 'living-space', 'kitchen-space'])) {
                    $fields[$currentInternalId]['fields'][$xmlReader->localName] = self::parseSubValue($xmlReader);
                }
            }

            if ($xmlReader->nodeType == \XMLReader::TEXT) {
                $fields[$currentInternalId]['fields'][$currentNode] = $xmlReader->value;
            }

            if ($xmlReader->nodeType == \XMLReader::END_ELEMENT && $xmlReader->localName == 'offer') {
                if ($downloadPictures) {
                    self::downloadPicture($fields, $memcache);
                } else {
                    self::save($fields, $memcache);
                }

                Debug::dumpToFile($index, 'INDEX', 'upload/progress.txt');
                unset($fields);
                $fields = [];

                $index++;
            }
        }

        $xmlReader->close();

        if (!$downloadPictures) {
            self::afterProcess();
        }
    }

    /**
     * Парсит следующие конструкции:
     * <area>
     *  <value>Значение для получения</value>
     * </area>
     * @param $xmlReader
     * @return mixed
     */
    protected static function parseSubValue(&$xmlReader) {
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == \XMLReader::TEXT) {
                return $xmlReader->value;
            }
        }
    }

    protected static function save($fields, $cache)
    {
        //TODO: REFACTORING CONST NUMBER IBLOCK
        $IBLOCK_COMPLEX = 1;
        $IBLOCK_TOWNAREA = 2;
        $IBLOCK_BUILDING = 6;
        $IBLOCK_BUILDING_TYPE = 9;
        $IBLOCK_RENOVATION = 10;
        $IBLOCK_FLOORS = 12;
        $IBLOCK_FLAT = 13;
        $IBLOCK_ROOMS = 14;
        $IBLOCK_LOCALITY = 17;
        $IBLOCK_BUILDING_PHASE = 24;
        $IBLOCK_REGION = 25;
        $IBLOCK_BATHROOM_UNIT = 27;
        $IBLOCK_BALCONY = 28;
        $IBLOCK_BUILDING_STATE = 29;

        $nodeOffer = new \Fgsoft\Nmarket\Node\OfferNode($fields);

        //TODO: REFACTORING ADD FABRIC SAVER
        //Сначала заполняем справочники
        $localitySaver = new DictionarySaver($nodeOffer, FabricExternalId::getForLocalityName($nodeOffer), $IBLOCK_LOCALITY, 'locality-name', $cache);
        $localitySaver->save();

        $regionSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForRegion($nodeOffer), $IBLOCK_REGION, 'region', $cache);
        $regionSaver->save();

        //Район
        $dictionarySaver = new DictionarySaver($nodeOffer, FabricExternalId::getForSubLocalityName($nodeOffer), $IBLOCK_TOWNAREA, 'sub-locality-name', $cache);
        $dictionarySaver->save();

        $districtSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForDistrict($nodeOffer), $IBLOCK_TOWNAREA, 'district', $cache);
        $districtSaver->save();

        //Ремонт
        $renovationSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForRenovation($nodeOffer), $IBLOCK_RENOVATION, 'renovation', $cache);
        $renovationSaver->save();

        //Возможные количества комнат
        $roomsSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForRooms($nodeOffer), $IBLOCK_ROOMS, 'rooms', $cache);
        $roomsSaver->save();

        //Тип дома
        $buildingTypeSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBuildingType($nodeOffer), $IBLOCK_BUILDING_TYPE, 'building-type', $cache);
        $buildingTypeSaver->save();

        //Фазы строительства
        $buildingPhaseSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBuildingPhase($nodeOffer), $IBLOCK_BUILDING_PHASE, 'building-phase', $cache);
        $buildingPhaseSaver->save();

        //Стадия строительства дома
        $buildingStateSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBuildingState($nodeOffer), $IBLOCK_BUILDING_STATE, 'building-state', $cache);
        $buildingStateSaver->save();

        //Тип балкона
        $balconySaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBalcony($nodeOffer), $IBLOCK_BALCONY, 'balcony', $cache);
        $balconySaver->save();

        //Тип балкона
        $bathroomUnitSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBathroomUnit($nodeOffer), $IBLOCK_BATHROOM_UNIT, 'bathroom-unit', $cache);
        $bathroomUnitSaver->save();

        //Сохранение комплекса
        $complexSaver = new ComplexSaver($nodeOffer, FabricExternalId::getForComplex($nodeOffer), $IBLOCK_COMPLEX, 'nmarket-complex-id', $cache);
        $complexSaver->save();


        //Сохранение корпуса
        $buildingSaver = new BuildingSaver($nodeOffer, FabricExternalId::getForBuilding($nodeOffer), $IBLOCK_BUILDING, 'nmarket-building-id', $cache);
        $buildingSaver->save();

        //Создаем этажи
         $floorSaver = new FloorSaver($nodeOffer, FabricExternalId::getForFloor($nodeOffer), $IBLOCK_FLOORS, 'floor', $cache);
         $floorSaver->save();

         //Сохранение кварир
        $flatSaver = new FlatSaver($nodeOffer, FabricExternalId::getForFlat($nodeOffer), $IBLOCK_FLAT, $cache);
        $flatSaver->save();
    }

    protected static function downloadPicture($fields, Cache $cache)
    {
        $node = new \Fgsoft\Nmarket\Node\OfferNode($fields);
        //TODO: REFACTORING MAGIC NUMBER 13
        $flatSaver = new FlatSaver($node, FabricExternalId::getForFlat($node), 13, $cache);

        if ($flatSaver->isNeedSave()) {
            $path = Application::getDocumentRoot() . '/upload/' . \COption::GetOptionString('fgsoft.nmarket', 'feedUploadPath', 'nmarket');

            try {
                if (null == $node->getImage()) {
                    return;
                }
                $pictureSaver = new PictureSave($node, FabricExternalId::getForFlat($node), $path);
                $pictureSaver->save();
            } catch (\Exception $e) {
                self::$logger->add(new Log('IMAGE_SAVE', 'Не сохранилось изображения offer = ' . $node->getInternalID()));
            }

        }
    }

    protected static function beforeProcess()
    {
        $sql = "UPDATE b_iblock_element_property set VALUE=\"N\" WHERE IBLOCK_PROPERTY_ID in (SELECT b.ID from b_iblock_property as b WHERE b.CODE=\"UPDATED_NOW\")";
        \Bitrix\Main\HttpApplication::getConnection()->query($sql);
    }

    protected static function afterProcess()
    {
        ///Деактивируем элементы, которые не были обновлены в выгрузке
//        $rs = \CIBlockElement::GetList(
//            [],
//            [
//                //TODO: REFACTORING MAGIC NUMBERS
//                'IBLOCK_ID' => [13],
//                [
//                    'LOGIC' => 'OR',
//                    ['=PROPERTY_UPDATED_NOW' => false],
//                    ['=PROPERTY_UPDATED_NOW' => 'N']
//                ]
//            ],
//            false,
//            false,
//            ['ID', 'IBLOCK_ID', 'PROPERTY_UPDATED_NOW']
//        );
//
//        while ($arItem = $rs->Fetch()) {
//            $ciElement = new \CIBlockElement();
//            $ciElement->Update(
//                $arItem['ID'],
//                [
//                    'ACTIVE' => 'N'
//                ]
//            );
//        }

        //Заполнение параметрых необходимых для сортировки
        $arDistricts = \Bitrix\Iblock\ElementTable::getList(['select' => ['ID'], 'filter' => ['IBLOCK_ID' => DISTRICT_IBLOCK_ID, 'ACTIVE' => 'Y']])->fetchAll();
        foreach ($arDistricts as $index => $arItem) {
            $strMinPrice = self::getNmarketMin(FLAT_IBLOCK_ID, 'METER_PRICE', ['PROPERTY_DISTRICT' => $arItem['ID']]);
            $strMaxPrice = self::getNmarketMax(FLAT_IBLOCK_ID, 'METER_PRICE', ['PROPERTY_DISTRICT' => $arItem['ID']]);
            $strMinSquare = self::getNmarketMin(FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $arItem['ID']]);
            $strMaxSquare = self::getNmarketMax(FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $arItem['ID']]);

            $arPropertiesValue = [];
            if ($strMinPrice) {
                $arPropertiesValue['MIN_METER_PRICE'] = $strMinPrice;
            }
            if ($strMaxPrice) {
                $arPropertiesValue['MAX_METER_PRICE'] = $strMaxPrice;
            }

            if ($strMinSquare) {
                $arPropertiesValue['MIN_SQUARE'] = $strMinSquare;
            }
            if ($strMaxSquare) {
                $arPropertiesValue['MAX_SQUARE'] = $strMaxSquare;
            }

            if (!empty($arPropertiesValue)) {
                \CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    DISTRICT_IBLOCK_ID,
                    $arPropertiesValue
                );
            }
        }


        self::saveFlatDataGroupByRoomNumber();
        /**
         * Функция сохраняет в Highload сгруппированные по количеству комнат данные:
         *  - мин/макс площадь
         *  - количество квартир всего
         *  - количество квартир в продаже
         *  - фильтр PROPERTY_ROOM_NUMBER = ROOM_NUMBER_ID из справочника
         */
    }
    public static function saveFlatDataGroupByRoomNumber()
    {
        $ROOM_NUMBER_IBLOCK_ID = 14;
        $FLAT_IBLOCK_ID = 13;

        $arResult = [];

        //1. Получаем данные по имеющимся количествам комнат
        $arFlatNumbers = [];
        $rsFlatNumbers = \Bitrix\Iblock\ElementTable::getList(['select' => ['ID'], 'filter' => ['=IBLOCK_ID' => $ROOM_NUMBER_IBLOCK_ID]]);
        while ($arItem = $rsFlatNumbers->fetch()) {
            $arFlatNumbers[] = $arItem['ID'];
        }
//    'UF_DISTRICT_ID' => 1,
//        'UF_ROOM_NUMBERS' => 123,
//        'UF_FLAT_ALL' => 100,
//        'UF_FLAT_SELL' => 50,
//        'UF_SQUARE_MIN' => (float)100,
//        'UF_SQUARE_MAX' => (float)200,
//        'UF_FILTER_STRING' => '=PROPERTY_ROOM=123'
        //2. Для каждого типа получаем количество квартир всего
        $rsCountAll = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $FLAT_IBLOCK_ID],
            ['PROPERTY_DISTRICT', 'PROPERTY_ROOM_NUMBER'],
            false

        );
        while ($arItem = $rsCountAll->Fetch()) {
            $arResult[$arItem['PROPERTY_DISTRICT_VALUE']][$arItem['PROPERTY_ROOM_NUMBER_VALUE']] = [
                'UF_DISTRICT_ID' => $arItem['PROPERTY_DISTRICT_VALUE'],
                'UF_ROOM_NUMBERS' => $arItem['PROPERTY_ROOM_NUMBER_VALUE'],
                'UF_FLAT_ALL' => $arItem['CNT']
            ];
        }

//3. Для каждого типа получаем количество в продаже
        $rsCountSale = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $FLAT_IBLOCK_ID, 'ACTIVE' => 'Y'],
            ['PROPERTY_DISTRICT', 'PROPERTY_ROOM_NUMBER'],
            false
        );
        while ($arItem = $rsCountSale->Fetch()) {
            $arResult[$arItem['PROPERTY_DISTRICT_VALUE']][$arItem['PROPERTY_ROOM_NUMBER_VALUE']]['UF_FLAT_SELL'] = $arItem['CNT'];
        }

//4. Получаем площади мин и макс и заполняем фильтр
        foreach ($arResult as $intDistrictID => $arItem) {
            foreach ($arItem as $intFlatNumberID => $arData) {
                $arResult[$intDistrictID][$intFlatNumberID]['UF_SQUARE_MAX'] = self::getNmarketMax($FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $intDistrictID, 'PROPERTY_ROOM_NUMBER' => $intFlatNumberID]);
                $arResult[$intDistrictID][$intFlatNumberID]['UF_SQUARE_MIN'] = self::getNmarketMin($FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $intDistrictID, 'PROPERTY_ROOM_NUMBER' => $intFlatNumberID]);
                $arResult[$intDistrictID][$intFlatNumberID]['UF_FILTER_STRING'] = 'PROPERTY_ROOM_NUMBER=' . $arData['UF_ROOM_NUMBERS'];
            }

        }

        foreach ($arResult as $intDistrictID => $arItem) {
            foreach ($arItem as $intRoomNumberID => $arData) {
                if ($arFlatParam = \Fgsoft\Nmarket\Model\FlatParamTable::getList(['select' => ['ID'], 'filter' => ['UF_DISTRICT_ID' => $intDistrictID, 'UF_ROOM_NUMBERS' => $intRoomNumberID]])->fetch()) {
                    \Fgsoft\Nmarket\Model\FlatParamTable::update(
                        $arFlatParam['ID'],
                        $arData
                    );
                } else {
                    \Fgsoft\Nmarket\Model\FlatParamTable::add($arData);
                }
            }
        }
    }

    public static function getNmarketMin($IBLOCK_ID, $PROPERTY_CODE, $arFilter)
    {
        return self::getNmarketPropertyValue($IBLOCK_ID, $PROPERTY_CODE, 'ASC', $arFilter);
    }

    public static function getNmarketMax($IBLOCK_ID, $PROPERTY_CODE, $arFilter)
    {
        return self::getNmarketPropertyValue($IBLOCK_ID, $PROPERTY_CODE, 'DESC', $arFilter);
    }

    public static function getNmarketPropertyValue($IBLOCK_ID, $PROPERTY_CODE, $strOrder, $arFilter)
    {
        $arDefaultFilter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $IBLOCK_ID,
            '!PROPERTY_' . $PROPERTY_CODE => false
        ];

        if (!empty($arFilter)) {
            $arDefaultFilter = array_merge($arDefaultFilter, $arFilter);
        }
        $arElement =  \CIBlockElement::GetList(
            ['PROPERTY_' . $PROPERTY_CODE => $strOrder],
            $arDefaultFilter,
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'PROPERTY_' . $PROPERTY_CODE]
        )->Fetch();

        if (empty($arElement['PROPERTY_' . $PROPERTY_CODE . '_VALUE'])) {
            return false;
        }

        return $arElement['PROPERTY_' . $PROPERTY_CODE . '_VALUE'];
    }
}