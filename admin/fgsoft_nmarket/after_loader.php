<?php require_once(dirname(dirname(dirname(__DIR__))) . "/bitrix/modules/main/include/prolog_before.php");
/**
 * Действия выполняемые после загрузки
 * User: Nikolay Mesherinov
 * Date: 24.08.17
 * Time: 10:16
 */
$start = microtime(true);
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('fgsoft.esbn');
//TODO: после тестов запилить защиту от прямого доступа через http, работа только через CRON


//Заполнение параметрых необходимых для сортировки
$arDistricts = \Bitrix\Iblock\ElementTable::getList(['select' => ['ID'], 'filter' => ['IBLOCK_ID' => DISTRICT_IBLOCK_ID, 'ACTIVE' => 'Y']])->fetchAll();
foreach ($arDistricts as $index => $arItem) {
    $strMinPrice = getMin(FLAT_IBLOCK_ID, 'METER_PRICE', ['PROPERTY_DISTRICT' => $arItem['ID']]);
    $strMaxPrice = getMax(FLAT_IBLOCK_ID, 'METER_PRICE', ['PROPERTY_DISTRICT' => $arItem['ID']]);
    $strMinSquare = getMin(FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $arItem['ID']]);
    $strMaxSquare = getMax(FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $arItem['ID']]);

    $arPropertiesValue = [];
    if ($strMinPrice) {
        $arPropertiesValue['MIN_METER_PRICE'] = $strMinPrice;
    }
    if ($strMaxPrice) {
        $arPropertiesValue['MAX_METER_PRICE'] = $strMaxPrice;
    }

    if ($strMinSquare) {
        $arPropertiesValue['MIN_SQUARE'] = $strMinSquare;
    }
    if ($strMaxSquare) {
        $arPropertiesValue['MAX_SQUARE'] = $strMaxSquare;
    }

    if (!empty($arPropertiesValue)) {
        \CIBlockElement::SetPropertyValuesEx(
            $arItem['ID'],
            DISTRICT_IBLOCK_ID,
           $arPropertiesValue
        );
    }
}


saveFlatDataGroupByRoomNumber();
/**
 * Функция сохраняет в Highload сгруппированные по количеству комнат данные:
 *  - мин/макс площадь
 *  - количество квартир всего
 *  - количество квартир в продаже
 *  - фильтр PROPERTY_ROOM_NUMBER = ROOM_NUMBER_ID из справочника
 */
function saveFlatDataGroupByRoomNumber()
{
    $ROOM_NUMBER_IBLOCK_ID = 14;
    $FLAT_IBLOCK_ID = 13;

    $arResult = [];

    //1. Получаем данные по имеющимся количествам комнат
    $arFlatNumbers = [];
    $rsFlatNumbers = \Bitrix\Iblock\ElementTable::getList(['select' => ['ID'], 'filter' => ['=IBLOCK_ID' => $ROOM_NUMBER_IBLOCK_ID]]);
    while ($arItem = $rsFlatNumbers->fetch()) {
        $arFlatNumbers[] = $arItem['ID'];
    }
//    'UF_DISTRICT_ID' => 1,
//        'UF_ROOM_NUMBERS' => 123,
//        'UF_FLAT_ALL' => 100,
//        'UF_FLAT_SELL' => 50,
//        'UF_SQUARE_MIN' => (float)100,
//        'UF_SQUARE_MAX' => (float)200,
//        'UF_FILTER_STRING' => '=PROPERTY_ROOM=123'
    //2. Для каждого типа получаем количество квартир всего
    $rsCountAll = \CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $FLAT_IBLOCK_ID],
        ['PROPERTY_DISTRICT', 'PROPERTY_ROOM_NUMBER'],
        false

    );
    while ($arItem = $rsCountAll->Fetch()) {
        $arResult[$arItem['PROPERTY_DISTRICT_VALUE']][$arItem['PROPERTY_ROOM_NUMBER_VALUE']] = [
            'UF_DISTRICT_ID' => $arItem['PROPERTY_DISTRICT_VALUE'],
            'UF_ROOM_NUMBERS' => $arItem['PROPERTY_ROOM_NUMBER_VALUE'],
            'UF_FLAT_ALL' => $arItem['CNT']
        ];
    }

//3. Для каждого типа получаем количество в продаже
    $rsCountSale = \CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $FLAT_IBLOCK_ID, 'ACTIVE' => 'Y'],
        ['PROPERTY_DISTRICT', 'PROPERTY_ROOM_NUMBER'],
        false
    );
    while ($arItem = $rsCountSale->Fetch()) {
        $arResult[$arItem['PROPERTY_DISTRICT_VALUE']][$arItem['PROPERTY_ROOM_NUMBER_VALUE']]['UF_FLAT_SELL'] = $arItem['CNT'];
    }

//4. Получаем площади мин и макс и заполняем фильтр
    foreach ($arResult as $intDistrictID => $arItem) {
        foreach ($arItem as $intFlatNumberID => $arData) {
            $arResult[$intDistrictID][$intFlatNumberID]['UF_SQUARE_MAX'] = getMax($FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $intDistrictID, 'PROPERTY_ROOM_NUMBER' => $intFlatNumberID]);
            $arResult[$intDistrictID][$intFlatNumberID]['UF_SQUARE_MIN'] = getMin($FLAT_IBLOCK_ID, 'SQUARE', ['PROPERTY_DISTRICT' => $intDistrictID, 'PROPERTY_ROOM_NUMBER' => $intFlatNumberID]);
            $arResult[$intDistrictID][$intFlatNumberID]['UF_FILTER_STRING'] = 'PROPERTY_ROOM_NUMBER=' . $arData['UF_ROOM_NUMBERS'];
        }

    }

    foreach ($arResult as $intDistrictID => $arItem) {
        foreach ($arItem as $intRoomNumberID => $arData) {
            if ($arFlatParam = \Fgsoft\Esbn\Model\FlatParamTable::getList(['select' => ['ID'], 'filter' => ['UF_DISTRICT_ID' => $intDistrictID, 'UF_ROOM_NUMBERS' => $intRoomNumberID]])->fetch()) {
                \Fgsoft\Esbn\Model\FlatParamTable::update(
                    $arFlatParam['ID'],
                    $arData
                );
            } else {
                \Fgsoft\Esbn\Model\FlatParamTable::add($arData);
            }
        }
    }
}

echo microtime(true) - $start;

function getMin($IBLOCK_ID, $PROPERTY_CODE, $arFilter)
{
    return getPropertyValue($IBLOCK_ID, $PROPERTY_CODE, 'ASC', $arFilter);
}

function getMax($IBLOCK_ID, $PROPERTY_CODE, $arFilter)
{
    return getPropertyValue($IBLOCK_ID, $PROPERTY_CODE, 'DESC', $arFilter);
}

function getPropertyValue($IBLOCK_ID, $PROPERTY_CODE, $strOrder, $arFilter)
{
    $arDefaultFilter = [
        'ACTIVE' => 'Y',
        'IBLOCK_ID' => $IBLOCK_ID,
        '!PROPERTY_' . $PROPERTY_CODE => false
    ];

    if (!empty($arFilter)) {
        $arDefaultFilter = array_merge($arDefaultFilter, $arFilter);
    }
    $arElement =  \CIBlockElement::GetList(
        ['PROPERTY_' . $PROPERTY_CODE => $strOrder],
        $arDefaultFilter,
        false,
        ['nTopCount' => 1],
        ['ID', 'IBLOCK_ID', 'PROPERTY_' . $PROPERTY_CODE]
    )->Fetch();

    if (empty($arElement['PROPERTY_' . $PROPERTY_CODE . '_VALUE'])) {
        return false;
    }

    return $arElement['PROPERTY_' . $PROPERTY_CODE . '_VALUE'];
}