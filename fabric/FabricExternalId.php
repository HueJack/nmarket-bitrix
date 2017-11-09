<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 16:36
 */

namespace Fgsoft\Nmarket\Fabric;


class FabricExternalId
{
    public static function get($node, $type)
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
     * Генератор ключа для района
     * @param $node
     * @return BuildingExternalId|FlatExternalId|FloorExternalId
     */
    public static function getForTownarea($node)
    {
        return static::get('sub-locality-name');
    }
}