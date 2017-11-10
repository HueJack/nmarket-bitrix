<?php
/**
 * User: Nikolay Mesherinov
 * Date: 20.10.2017
 * Time: 11:27
 */
namespace Fgsoft\Nmarket\Model;

use \Bitrix\Main\Entity\DataManager;
use \Bitrix\Main\Entity\FloatField;
use \Bitrix\Main\Entity\IntegerField;
use \Bitrix\Main\Entity\ReferenceField;
use \Bitrix\Main\Entity\StringField;

class FlatParamTable extends DataManager
{
    public static function getTableName()
    {
        return 'flats_params_group_by_rooms';
    }

    public static function getMap()
    {
        return [
            'ID' => new IntegerField('ID', [
                'primary' => 'true',
                'autocomplete' => 'true',
                'title' => 'ID'
            ]),
            'UF_DISTRICT_ID' => new IntegerField('UF_DISTRICT_ID', [
                'required' => true,
                'title' => 'ID ЖК'
            ]),
            'UF_ROOM_NUMBERS' => new IntegerField('UF_ROOM_NUMBERS', [
                'required' => true,
                'title' => 'Количество комнат ID'
            ]),
            'UF_FLAT_ALL' => new IntegerField('UF_FLAT_ALL', [
                'required' => true,
                'title' => 'Количество квартир всего'
            ]),
            'UF_FLAT_SELL' => new IntegerField('UF_FLAT_SELL', [
                'required' => true,
                'title' => 'Количество квартир в продаже'
            ]),
            'UF_SQUARE_MIN' => new FloatField('UF_SQUARE_MIN', [
                'required' => true,
                'title' => 'Минимальная площадь среди квартир'
            ]),
            'UF_SQUARE_MAX' => new FloatField('UF_SQUARE_MAX', [
                'required' => true,
                'title' => 'Максимальная площадь среди квартир'
            ]),
            'UF_FILTER_STRING' => new StringField('UF_FILTER_STRING', [
                'required' => true,
                'title' => 'Фильтр по комнатам'
            ]),
            'ROOM' => new ReferenceField(
                'ROOM',
                '\Bitrix\Iblock\ElementTable',
                ['=this.UF_ROOM_NUMBERS' => 'ref.ID'],
                ['join_type' => 'LEFT']
            )
        ];
    }
}