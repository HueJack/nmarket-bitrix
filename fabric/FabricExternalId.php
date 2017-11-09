<?php
/**
 * Возвращает генератор внешнего ключа
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 16:36
 */

namespace Fgsoft\Nmarket\Fabric;


use Fgsoft\Nmarket\ExternalId\DictionaryExternalId;
use Fgsoft\Nmarket\ExternalId\RealExternalId;
use Fgsoft\Nmarket\ExternalId\BuildingExternalId;
use Fgsoft\Nmarket\ExternalId\FloorExternalId;
use Fgsoft\Nmarket\ExternalId\FlatExternalId;
use Fgsoft\Nmarket\Node\Node;

class FabricExternalId
{
    public static function get(Node $node, $type)
    {
        switch ($type) {
            case 'sub-locality-name':
                return new DictionaryExternalId($node, 'sub-locality-name');
                break;
            case 'renovation':
                return new DictionaryExternalId($node, 'renovation', 'renovation');
                break;
            case 'rooms':
                return new DictionaryExternalId($node, 'rooms', 'rooms');
                break;
             case 'building-type':
                return new DictionaryExternalId($node, 'building-type');
                break;
            case 'building-phase':
                return  new DictionaryExternalId($node, 'building-phase', 'building-phase');
                break;
            case 'nmarket-complex-id':
                return new RealExternalId($node, 'nmarket-complex-id');
                break;
            case 'nmarket-building-id':
                return new BuildingExternalId($node);
                break;
            case 'floor':
                return new FloorExternalId($node);
                break;
            case 'flat':
                return new FlatExternalId($node);
                break;
            default:
                throw new \Exception('Ошибка! Нет такого ExternalID');
                break;
        }
    }

    /**
     * Для района
     * @param $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForTownarea(Node $node)
    {
        return static::get($node, 'sub-locality-name');
    }

    /**
     * Для Ремонта
     * @param $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForRenovation(Node $node)
    {
        return static::get($node, 'renovation');
    }

    /**
     * Для этажей
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForRooms(Node $node)
    {
        return static::get($node, 'rooms');
    }

    /**
     * Тип дома
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForBuildingType(Node $node)
    {
        return static::get($node, 'building-type');
    }

    /**
     * Фазы строительства
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForBuildingPhase(Node $node)
    {
        return static::get($node, 'building-phase');
    }

    /**
     * Комплекс
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForComplex(Node $node)
    {
        return static::get($node, 'nmarket-complex-id');
    }

    /**
     * Строение/Корпус
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForBuilding(Node $node)
    {
        return static::get($node, 'nmarket-building-id');
    }

    /**
     * Этаж
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForFloor(Node $node)
    {
        return static::get($node, 'floor');
    }

    /**
     * Квартира
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForFlat(Node $node)
    {
        return static::get($node, 'flat');
    }
}