<?php
/**
 * Фасад для формирования всей логики работы с объектами
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:53
 */

namespace Fgsoft\Nmarket\Facade;


use Fgsoft\Nmarket\ExternalId\BuildingExternalId;
use Fgsoft\Nmarket\ExternalId\DictionaryExternalId;
use Fgsoft\Nmarket\ExternalId\FlatExternalId;
use Fgsoft\Nmarket\ExternalId\FloorExternalId;
use Fgsoft\Nmarket\ExternalId\RealExternalId;
use Fgsoft\Nmarket\Saver\BuildingSaver;
use Fgsoft\Nmarket\Saver\ComplexSaver;
use Fgsoft\Nmarket\Saver\DictionarySaver;
use Fgsoft\Nmarket\Saver\FlatSaver;
use Fgsoft\Nmarket\Saver\FloorSaver;

class FacadeProcessing
{
    public static function process($fields)
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
        $dictionarySaver = new DictionarySaver($nodeOffer, new DictionaryExternalId($nodeOffer, 'sub-locality-name'), $IBLOCK_TOWNAREA, 'sub-locality-name');
        $dictionarySaver->save();

        //Ремонт
        $renovationSaver = new DictionarySaver($nodeOffer, new DictionaryExternalId($nodeOffer, 'renovation', 'renovation'), $IBLOCK_RENOVATION, 'renovation');
        $renovationSaver->save();

        //Возможные количества комнат
        $roomsSaver = new DictionarySaver($nodeOffer, new DictionaryExternalId($nodeOffer, 'rooms', 'rooms'), $IBLOCK_ROOMS, 'rooms');
        $roomsSaver->save();

        //Тип дома
        $buildingTypeSaver = new DictionarySaver($nodeOffer, new DictionaryExternalId($nodeOffer, 'building-type'), $IBLOCK_BUILDING_TYPE, 'building-type');
        $buildingTypeSaver->save();

        //Фазы строительства
        $buildingPhaseSaver = new DictionarySaver($nodeOffer, new DictionaryExternalId($nodeOffer, 'building-phase', 'building-phase'), $IBLOCK_BUILDING_PHASE, 'building-phase');
        $buildingPhaseSaver->save();

        //Сохранение комплекса
        $complexSaver = new ComplexSaver($nodeOffer, new RealExternalId($nodeOffer, 'nmarket-complex-id'), $IBLOCK_COMPLEX, 'nmarket-complex-id');
        $complexSaver->save();

        //Сохранение корпуса
        $buildingSaver = new BuildingSaver($nodeOffer, new BuildingExternalId($nodeOffer), $IBLOCK_BUILDING, 'nmarket-building-id');
        $buildingSaver->save();

        //Создаем этажи
         $floorSaver = new FloorSaver($nodeOffer, new FloorExternalId($nodeOffer), $IBLOCK_FLOORS, 'floor');
         $floorSaver->save();

         //Сохранение кварир
        $flatSaver = new FlatSaver($nodeOffer, new FlatExternalId($nodeOffer), $IBLOCK_FLAT);
        $flatSaver->save();
    }
}