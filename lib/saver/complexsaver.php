<?php
/**
 * Жилищные комплексы
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 12:33
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\Fabric\FabricExternalId;

class ComplexSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getBuildingName());
        $this->addField('CODE', \CUtil::translit($this->node->getBuildingName(), 'ru'));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);
        $this->addField('DETAIL_TEXT', $this->node->getDescription());
        $this->addField('ACTIVE', 'N');

        $this->addProperty('ADDRESS', $this->node->getAddress());
        $this->addProperty('UPDATED_NOW', 'Y');

        $propertiesData = $this->getPropertyValuesByExternals([
            'TOWNAREA' => FabricExternalId::getForSubLocalityName($this->node),
            'DISTRICT' => FabricExternalId::getForDistrict($this->node),
            'LOCALITY_NAME' => FabricExternalId::getForLocalityName($this->node),
            'NEARMETRO' => FabricExternalId::getForMetro($this->node),
        ]);

        if (false !== $propertiesData && !empty($propertiesData)) {
            foreach ($propertiesData as $item) {
                $this->addProperty($item['PROPERTY_CODE'], $item['ID']);
            }
        }
    }
}