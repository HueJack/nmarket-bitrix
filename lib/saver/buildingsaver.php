<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 12:33
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\ExternalId\DictionaryExternalId;
use Fgsoft\Nmarket\ExternalId\RealExternalId;
use Fgsoft\Nmarket\Fabric\FabricExternalId;

class BuildingSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getBuildingSection());
//        $this->addField('CODE', \CUtil::translit($this->node->getBuildingName() . $this->node->getBuildingSection(), 'ru'));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);
        $this->addField('ACTIVE', 'N');

        $this->addProperty('ENDING_YEAR', $this->node->getBuildYear());
        $this->addProperty('ENDING_QUARTER', $this->node->getReadyQuarter());
        $this->addProperty('FLOORS', $this->node->getFloorsTotal());
        $this->addProperty('DONT_NEED_UPDATE', 'Y');

        $propertiesData = static::getPropertyValuesByExternals([
            'DISTRICT' => FabricExternalId::getForComplex($this->node),
            'BUILDING_PHASE' => FabricExternalId::getForBuildingPhase($this->node),
            'BUILDING_TYPE' => FabricExternalId::getForBuildingType($this->node)
        ]);

        if (false !== $propertiesData && !empty($propertiesData)) {
            foreach ($propertiesData as $item) {
                $this->addProperty($item['PROPERTY_CODE'], $item['ID']);
            }
        }
    }

    protected function isNeedSave()
    {
        //Если не активен ЖК, то не обрабатываем корпус
        return \Bitrix\Iblock\ElementTable::getList([
            'select' => ['ID'],
            'filter' => [
                'ACTIVE' => 'Y',
                'XML_ID' => FabricExternalId::getForComplex($this->node)->get()
            ],
            'limit' => 1
        ])->fetch();
    }
}