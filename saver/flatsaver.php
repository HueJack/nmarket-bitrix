<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:48
 */

namespace Fgsoft\Nmarket\Saver;


use Fgsoft\Nmarket\ExternalId\BuildingExternalId;
use Fgsoft\Nmarket\ExternalId\DictionaryExternalId;
use Fgsoft\Nmarket\ExternalId\FloorExternalId;
use Fgsoft\Nmarket\ExternalId\RealExternalId;

class FlatSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getInternalID());
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);
        $this->addField('DETAIL_PICTURE', $this->node->getImage());

        $this->addProperty('SQUARE', $this->node->getArea());
        $this->addProperty('APARTMENT_PRICE', $this->node->getPrice());
        $this->addProperty('METER_PRICE', $this->node->getPrice()/$this->node->getArea());
        $this->addProperty('LIVING_SQUARE', $this->node->getLivingSpace());
        $this->addProperty('KITCHEN_SQUARE', $this->node->getKitchenSpace());
        $this->addProperty('CEILING_HEIGHT', $this->node->getCeilingHeight());
        $this->addProperty('BALCONY', $this->node->getBalcony());
        $this->addProperty('BATHROOM_UNIT', $this->node->getBathroomUnit());

        $complex = self::getByExternalId(new RealExternalId($this->node, 'nmarket-complex-id'));
        if (!empty($complex['ID'])) {
            $this->addProperty('DISTRICT', $complex['ID']);
        }

        $building = self::getByExternalId(new BuildingExternalId($this->node));
        if (!empty($building['ID'])) {
            $this->addProperty('BUILDING', $building['ID']);
        }

        $floor = self::getByExternalId(new FloorExternalId($this->node));
        if (!empty($floor['ID'])) {
            $this->addProperty('FLOOR', $floor['ID']);
        }

        $renovation = self::getByExternalId(new DictionaryExternalId($this->node, 'renovation', 'renovation'));
        if (!empty($renovation['ID'])) {
            $this->addProperty('FACING', $renovation['ID']);
        }

        $roomNumber = self::getByExternalId(new DictionaryExternalId($this->node, 'rooms', 'rooms'));
        if (!empty($roomNumber['ID'])) {
            $this->addProperty('ROOM_NUMBER', $roomNumber['ID']);
        }
    }

}