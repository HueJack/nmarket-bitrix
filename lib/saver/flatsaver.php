<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:48
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\Fabric\FabricExternalId;

class FlatSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getInternalID());
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);
        $this->addField('PREVIEW_PICTURE', $this->node->getImage());

        $this->addProperty('SQUARE', $this->node->getArea());
        $this->addProperty('APARTMENT_PRICE', $this->node->getPrice());
        $this->addProperty('METER_PRICE', $this->node->getPrice()/$this->node->getArea());
        $this->addProperty('LIVING_SQUARE', $this->node->getLivingSpace());
        $this->addProperty('KITCHEN_SQUARE', $this->node->getKitchenSpace());
        $this->addProperty('CEILING_HEIGHT', $this->node->getCeilingHeight());

        $propertiesData = $this->getPropertyValuesByExternals([
            'DISTRICT' => FabricExternalId::getForComplex($this->node),
            'BUILDING' => FabricExternalId::getForBuilding($this->node),
            'FLOOR' => FabricExternalId::getForFloor($this->node),
            'FACING' => FabricExternalId::getForRenovation($this->node),
            'ROOM_NUMBER' => FabricExternalId::getForRooms($this->node),
            'BALCONY' => FabricExternalId::getForBalcony($this->node),
            'BATHROOM_UNIT' => FabricExternalId::getForBathroomUnit($this->node)
        ]);

        if (false !== $propertiesData && !empty($propertiesData)) {
            foreach ($propertiesData as $item) {
                $this->addProperty($item['PROPERTY_CODE'], $item['ID']);
            }
        }
    }

    protected function isNeedSave()
    {
        //Если корпус существует и активен, то грузим квартиры
        $result = [];
        $cacheKey = 'ACTIVE_' . FabricExternalId::getForBuilding($this->node)->get();

        if (!($result = $this->getFromCache($cacheKey))) {
            $result =  \Bitrix\Iblock\ElementTable::getList([
                'select' => ['ID'],
                'filter' => [
                    'ACTIVE' => 'Y',
                    'XML_ID' => FabricExternalId::getForBuilding($this->node)->get()
                ]
            ])->fetch();

            $this->setToCache($cacheKey, $result);
        } else {
            echo 'isNeedSave из кэша <br>';
        }

        return $result;
    }
}