<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 12:33
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\ExternalId\DictionaryExternalId;

class ComplexSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getBuildingName());
        $this->addField('CODE', \CUtil::translit($this->node->getBuildingName(), 'ru'));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);

        $this->addProperty('ADDRESS', $this->node->getAddress());

        //Получаем район
        $district = self::getByExternalId(new DictionaryExternalId($this->node, 'sub-locality-name'));


        if (!empty($district['ID'])) {
            $this->addProperty('TOWNAREA', $district['ID']);
        }
    }
}