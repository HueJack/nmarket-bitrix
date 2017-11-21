<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:48
 */

namespace Fgsoft\Nmarket\Saver;


use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
use Fgsoft\Nmarket\Fabric\FabricExternalId;
use Fgsoft\Nmarket\Model\FilesTable;

class FlatSaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->getInternalID());
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);

        if (null !== ($imageUrl = $this->node->getImage())) {
            $imagePid = PictureSave::getPidByUrl($imageUrl);

            //TODO: REFACTOR GET FILE URL TO SETTINGS
            $filePath = FilesTable::getList([
                'select' => ['FILE_PATH'],
                'filter' => [
                    '=ELEMENT_XML_ID' => $this->externalId->get(),
                    '=PID' => $imagePid
                    ]
            ])->fetch();

            if (\Bitrix\Main\IO\File::isFileExists($filePath['FILE_PATH'])) {
                $this->addField('PREVIEW_PICTURE', $filePath['FILE_PATH']);
            } else {
              Debug::dumpToFile('Ошибка! Файла нет для XML_ID =' . $this->externalId->get(), 'upload/error-'.date('dmY') . '.txt');
            }
        }

        $this->addProperty('SQUARE', $this->node->getArea());
        $this->addProperty('APARTMENT_PRICE', $this->node->getPrice());
        $this->addProperty('METER_PRICE', $this->node->getPrice()/$this->node->getArea());
        $this->addProperty('LIVING_SQUARE', $this->node->getLivingSpace());
        $this->addProperty('KITCHEN_SQUARE', $this->node->getKitchenSpace());
        $this->addProperty('CEILING_HEIGHT', $this->node->getCeilingHeight());
        $this->addProperty('UPDATED_NOW', 'Y');

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

    public function isNeedSave()
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
        }

        return $result;
    }
}