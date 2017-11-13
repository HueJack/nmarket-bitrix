<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:48
 */

namespace Fgsoft\Nmarket\Saver;

use Fgsoft\Nmarket\Fabric\FabricExternalId;

class FloorSaver extends AbstractSaver
{
    public function fillFields()
    {
        $this->addField('NAME', $this->node->get($this->nodeKey));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);

        $building = self::getByExternalId(FabricExternalId::getForBuilding($this->node));
        if (!empty($building['ID'])) {
            $this->addProperty('BUILDING', $building['ID']);
        }

        $this->addProperty('DONT_NEED_UPDATE', 'Y');
    }

    protected function isNeedSave()
    {
        return \Bitrix\Iblock\ElementTable::getList([
            'select' => ['ID'],
            'filter' => [
                'ACTIVE' => 'Y',
                'XML_ID' => FabricExternalId::getForBuilding($this->node)->get()
            ]
        ])->fetch();
    }
}