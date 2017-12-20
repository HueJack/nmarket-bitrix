<?php
/**
 * Возвращает класс сохренения для типа данных
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 17:46
 */

namespace Fgsoft\Nmarket\Fabric;

use Fgsoft\Nmarket\Node\Node;
use Fgsoft\Nmarket\Saver;
use Fgsoft\Nmarket\Options\ModuleIblockList;

class FabricSaver
{
    public static function create(Node $nodeOffer, $nodeKey = '', $cache = null)
    {
        if (empty($nodeKey)) {
            throw new \InvalidArgumentException('Argument is empty');
        }

        switch ($nodeKey) {
            case 'locality-name':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForLocalityName($nodeOffer), ModuleIblockList::getLocalityIblockId(), $nodeKey, $cache);
                break;
            case 'region':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForRegion($nodeOffer), ModuleIblockList::getRegionIblockId(), $nodeKey, $cache);
                break;
            case 'sub-locality-name':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForSubLocalityName($nodeOffer), ModuleIblockList::getTownareaIblockId(), $nodeKey, $cache);
                break;
            case 'district':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForDistrict($nodeOffer), ModuleIblockList::getTownareaIblockId(), $nodeKey, $cache);
                break;
            case 'renovation':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForRenovation($nodeOffer), ModuleIblockList::getRenovationIblockId(), $nodeKey, $cache);
                break;
            case 'rooms':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForRooms($nodeOffer), ModuleIblockList::getRoomsIblockId(), $nodeKey, $cache);
                break;
            case 'building-type':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForBuildingType($nodeOffer), ModuleIblockList::getBuildingTypeIblockId(), $nodeKey, $cache);
                break;
            case 'building-phase':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForBuildingPhase($nodeOffer), ModuleIblockList::getBuildingPhaseIblockId(), $nodeKey, $cache);
                break;
            case 'building-state':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForBuildingState($nodeOffer), ModuleIblockList::getBuildingStateIblockId(), $nodeKey, $cache);
                break;
            case 'balcony':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForBalcony($nodeOffer), ModuleIblockList::getBalconyIblockId(), $nodeKey, $cache);
                break;
            case 'bathroom-unit':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForBathroomUnit($nodeOffer), ModuleIblockList::getBathroomUnitIblockId(), $nodeKey, $cache);
                break;
            case 'metro':
                return new Saver\DictionarySaver($nodeOffer, FabricExternalId::getForMetro($nodeOffer), ModuleIblockList::getMetroIblockId(), $nodeKey, $cache);
                break;
            case 'nmarket-complex-id':
                return new Saver\ComplexSaver($nodeOffer, FabricExternalId::getForComplex($nodeOffer), ModuleIblockList::getComplexIblockId(), $nodeKey, $cache);
                break;
            case 'nmarket-building-id':
                return new Saver\BuildingSaver($nodeOffer, FabricExternalId::getForBuilding($nodeOffer), ModuleIblockList::getBuildingIblockId(), $nodeKey, $cache);
                break;
            case 'floor':
                return new Saver\FloorSaver($nodeOffer, FabricExternalId::getForFloor($nodeOffer), ModuleIblockList::getFloorsIblockId(), $nodeKey, $cache);
                break;
            case 'flat':
                return new Saver\FlatSaver($nodeOffer, FabricExternalId::getForFlat($nodeOffer), ModuleIblockList::getFlatIblockId(), $cache);
                break;
            default:
                throw new \InvalidArgumentException('Ошибка! Переданный ключ не соответствует ни одному из вариантов');

        }
    }
}