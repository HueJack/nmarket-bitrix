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
            case 'locality-name':
                return new DictionaryExternalId($node, 'locality-name');
                break;
            case 'sub-locality-name':
                return new DictionaryExternalId($node, 'sub-locality-name');
                break;
            case 'renovation':
                return new DictionaryExternalId($node, 'renovation', 'renovation');
                break;
            case 'rooms':
                return new DictionaryExternalId($node, 'rooms', 'rooms');
                break;
            case 'region':
                return new DictionaryExternalId($node, 'region', 'region');
                break;
            case 'district':
                return new DictionaryExternalId($node, 'district', 'district');
                break;
             case 'building-type':
                return new DictionaryExternalId($node, 'building-type');
                break;
            case 'building-phase':
                return  new DictionaryExternalId($node, 'building-phase', 'building-phase');
                break;
            case 'building-state':
                return  new DictionaryExternalId($node, 'building-state', 'building-state');
                break;
            case 'balcony':
                return  new DictionaryExternalId($node, 'balcony', 'balcony');
                break;
            case 'bathroom-unit':
                return  new DictionaryExternalId($node, 'bathroom_unit', 'bathroom_unit');
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
     * Название субъекта РФ
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForRegion(Node $node)
    {
        return static::get($node, 'region');
    }

    /**
     * Название района субъекта РФ.
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForDistrict(Node $node)
    {
        return static::get($node, 'district');
    }


    /**
     * Населенный пункт
     * @param $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForLocalityName(Node $node)
    {
        return static::get($node, 'locality-name');
    }

    /**
     * Район населенного пункта.
     * @param $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForSubLocalityName(Node $node)
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
     * Количество комнат
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
     * Стадия строительства дома
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForBuildingState(Node $node)
    {
        return static::get($node, 'building-state');
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

    /**
     *
     * @param Node $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForBalcony(Node $node)
    {
        return static::get($node, 'balcony');
    }

    public static function getForBathroomUnit(Node $node)
    {
        return static::get($node, 'bathroom-unit');
    }
}