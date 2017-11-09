<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:48
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\ExternalId\BuildingExternalId;

class FloorSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->get($this->nodeKey));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);

        $building = self::getByExternalId(new BuildingExternalId($this->node));
        if (!empty($building['ID'])) {
            $this->addProperty('BUILDING', $building['ID']);
        }
    }

}