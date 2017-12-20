<?php
/**
 * User: Nikolay Mesherinov
 * Date: 18.12.2017
 * Time: 15:12
 */

namespace Fgsoft\Nmarket\Options;

use \Bitrix\Main\Config\Option;

class ModuleIblockList extends Option
{
    protected static $MODULE_IBLOCK_LIST = [
        'COMPLEX' => ['TITLE' => 'Комплекс'],
        'BUILDING' => ['TITLE' => 'Строение'],
        'FLOORS' => ['TITLE' => 'Этажи'],
        'FLAT' => ['TITLE' => 'Квартиры'],
        'ROOMS' => ['TITLE' => 'Возможные количества комнат'],

        'BUILDING_STATE' => ['TITLE' => 'Стадия строительства дома'],
        'REGION' => ['TITLE' => 'Регион'],
        'LOCALITY' => ['TITLE' => 'Населенный пункт'],
        'TOWNAREA' => ['TITLE' => 'Районы'],
        'METRO' => ['TITLE' => 'Станции метро'],

        'BUILDING_TYPE' => ['TITLE' => 'Тип корпуса'],
        'RENOVATION' => ['TITLE' => 'Ремонт'],
        'BUILDING_PHASE' => ['TITLE' => 'Очередь строительства'],
        'BATHROOM_UNIT' => ['TITLE' => 'Тип санузла'],
        'BALCONY' => ['TITLE' => 'Тип балкона'],
    ];

    public static function getModuleName()
    {
        return 'fgsoft.nmarket';
    }

    public static function getList()
    {
        return self::$MODULE_IBLOCK_LIST;
    }

    public static function getComplexIblockId()
    {
        return self::getIblockIdByKey('COMPLEX');
    }

    public static function getBuildingIblockId()
    {
        return self::getIblockIdByKey('BUILDING');
    }

    public static function getFloorsIblockId()
    {
        return self::getIblockIdByKey('FLOORS');
    }

    public static function getFlatIblockId()
    {
        return self::getIblockIdByKey('FLAT');
    }

    public static function getRoomsIblockId()
    {
        return self::getIblockIdByKey('ROOMS');
    }

    public static function getBuildingStateIblockId()
    {
        return self::getIblockIdByKey('BUILDING_STATE');
    }

    public static function getRegionIblockId()
    {
        return self::getIblockIdByKey('REGION');
    }

    public static function getLocalityIblockId()
    {
        return self::getIblockIdByKey('LOCALITY');
    }

    public static function getTownareaIblockId()
    {
        return self::getIblockIdByKey('TOWNAREA');
    }

    public static function getMetroIblockId()
    {
        return self::getIblockIdByKey('METRO');
    }

    public static function getBuildingTypeIblockId()
    {
        return self::getIblockIdByKey('BUILDING_TYPE');
    }

    public static function getRenovationIblockId()
    {
        return self::getIblockIdByKey('RENOVATION');
    }

    public static function getBuildingPhaseIblockId()
    {
        return self::getIblockIdByKey('BUILDING_PHASE');
    }

    public static function getBathroomUnitIblockId()
    {
        return self::getIblockIdByKey('BATHROOM_UNIT');
    }

    public static function getBalconyIblockId()
    {
        return self::getIblockIdByKey('BALCONY');
    }

    public static function getIblockIdByKey($key)
    {
        return self::get(self::getModuleName(), $key, null);
    }
}