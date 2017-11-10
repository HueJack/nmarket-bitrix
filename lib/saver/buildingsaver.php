<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 12:33
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\ExternalId\DictionaryExternalId;
use Fgsoft\Nmarket\ExternalId\RealExternalId;

class BuildingSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getBuildingSection());
        $this->addField('CODE', \CUtil::translit($this->node->getBuildingName() . $this->node->getBuildingSection(), 'ru'));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);

        $this->addProperty('ENDING_YEAR', $this->node->getBuildYear());
        $this->addProperty('ENDING_QUARTER', $this->node->getReadyQuarter());
        $this->addProperty('FLOORS', $this->node->getFloorsTotal());

        //Получаем комплекс
        $district = self::getByExternalId(new RealExternalId($this->node, 'nmarket-complex-id'));
        if (!empty($district['ID'])) {
            $this->addProperty('DISTRICT', $district['ID']);
        }

        //Получаем фазу строительства
        $buildingPhase = self::getByExternalId(new DictionaryExternalId($this->node, 'building-phase', 'building-phase'));
        if (!empty($buildingPhase['ID'])) {
            $this->addProperty('BUILDING_PHASE', $buildingPhase['ID']);
        }

        //ТИп корпуса
        $buildingType = self::getByExternalId(new DictionaryExternalId($this->node, 'building-type'));
        if (!empty($buildingType['ID'])) {
            $this->addProperty('BUILDING_TYPE', $buildingType['ID']);
        }
    }
}