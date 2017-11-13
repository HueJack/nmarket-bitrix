<?php
/**
 * Фасад для формирования всей логики работы с объектами
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:53
 */

namespace Fgsoft\Nmarket\Facade;

use Fgsoft\Nmarket\Fabric\FabricExternalId;
use Fgsoft\Nmarket\Saver\BuildingSaver;
use Fgsoft\Nmarket\Saver\ComplexSaver;
use Fgsoft\Nmarket\Saver\DictionarySaver;
use Fgsoft\Nmarket\Saver\FlatSaver;
use Fgsoft\Nmarket\Saver\FloorSaver;

class FacadeProcessing
{

    public static function process(\XMLReader $xmlReader)
    {
        $fields = [];
        $currentInternalId = 0;
        $currentNode = '';
        $index = 0;

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

                self::save($fields);


                unset($fields);
                $fields = [];

                if ($index == 500) {
                    break;
                }

                $index++;
            }
        }

        $xmlReader->close();
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

    protected static function save($fields)
    {
        $IBLOCK_COMPLEX = 1;
        $IBLOCK_TOWNAREA = 2;
        $IBLOCK_BUILDING = 6;
        $IBLOCK_BUILDING_TYPE = 9;
        $IBLOCK_RENOVATION = 10;
        $IBLOCK_FLOORS = 12;
        $IBLOCK_FLAT = 13;
        $IBLOCK_ROOMS = 14;
        $IBLOCK_BUILDING_PHASE = 24;

        $nodeOffer = new \Fgsoft\Nmarket\Node\OfferNode($fields);

        //Сначала заполняем справочники
        //Район
        $dictionarySaver = new DictionarySaver($nodeOffer, FabricExternalId::getForTownarea($nodeOffer), $IBLOCK_TOWNAREA, 'sub-locality-name');
        $dictionarySaver->save();

        //Ремонт
        $renovationSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForRenovation($nodeOffer), $IBLOCK_RENOVATION, 'renovation');
        $renovationSaver->save();

        //Возможные количества комнат
        $roomsSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForRooms($nodeOffer), $IBLOCK_ROOMS, 'rooms');
        $roomsSaver->save();

        //Тип дома
        $buildingTypeSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBuildingType($nodeOffer), $IBLOCK_BUILDING_TYPE, 'building-type');
        $buildingTypeSaver->save();

        //Фазы строительства
        $buildingPhaseSaver = new DictionarySaver($nodeOffer, FabricExternalId::getForBuildingPhase($nodeOffer), $IBLOCK_BUILDING_PHASE, 'building-phase');
        $buildingPhaseSaver->save();

        //Сохранение комплекса
        $complexSaver = new ComplexSaver($nodeOffer, FabricExternalId::getForComplex($nodeOffer), $IBLOCK_COMPLEX, 'nmarket-complex-id');
        $complexSaver->save();

        //Сохранение корпуса
        $buildingSaver = new BuildingSaver($nodeOffer, FabricExternalId::getForBuilding($nodeOffer), $IBLOCK_BUILDING, 'nmarket-building-id');
        $buildingSaver->save();

        //Создаем этажи
         $floorSaver = new FloorSaver($nodeOffer, FabricExternalId::getForFloor($nodeOffer), $IBLOCK_FLOORS, 'floor');
         $floorSaver->save();

         //Сохранение кварир
        $flatSaver = new FlatSaver($nodeOffer, FabricExternalId::getForFlat($nodeOffer), $IBLOCK_FLAT);
        $flatSaver->save();
    }
}