<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 12:33
 */

namespace Fgsoft\Nmarket\Saver;

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
        $this->addProperty('UPDATED_NOW', 'Y');

        $propertiesData = $this->getPropertyValuesByExternals([
            'DISTRICT' => FabricExternalId::getForComplex($this->node),
            'BUILDING_PHASE' => FabricExternalId::getForBuildingPhase($this->node),
            'BUILDING_TYPE' => FabricExternalId::getForBuildingType($this->node),
            'BUILDING_STATE' => FabricExternalId::getForBuildingState($this->node)
        ]);

        if (false !== $propertiesData && !empty($propertiesData)) {
            foreach ($propertiesData as $item) {
                $this->addProperty($item['PROPERTY_CODE'], $item['ID']);
            }
        }
    }
}