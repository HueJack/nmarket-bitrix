<?php
/**
 * User: Nikolay Mesherinov
 * Date: 17.11.2017
 * Time: 10:55
 */

namespace Fgsoft\Nmarket\Model;

use \Bitrix\Main\Entity;

class FilesTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'fgsoft_nmarket_files';
    }

    public static function getMap()
    {
        return [
            'ID' => new Entity\IntegerField('ID', [
                'primary' => 'true',
                'autocomplete' => 'true',
                'title' => 'ID'
            ]),
            'PID' => new Entity\StringField('PID', [
                'require' => true,
                'unique' => true
            ]),
            'FILE_PATH' => new Entity\StringField('FILE_PATH', [
                'require' => true,
                'unique' => true
            ]),
            'ELEMENT_XML_ID' => new Entity\StringField('ELEMENT_XML_ID', [
                'require' => true
            ])
        ];
    }
}