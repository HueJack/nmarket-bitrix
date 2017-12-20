<?php
/**
 * User: Nikolay Mesherinov
 * Date: 15.06.17
 * Time: 17:35
 */

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock;

Loc::loadLanguageFile(__FILE__);
Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');

$module_id = 'fgsoft.nmarket';

Loader::includeModule($module_id);
Loader::includeModule('iblock');

$rsIblocks = Iblock\IblockTable::getList(['select' => ['ID', 'NAME'], 'filter' => ['ACTIVE' => 'Y']]);
$arIblocks = [];
while ($arItem = $rsIblocks->fetch()) {
    $arIblocks[$arItem['ID']] = $arItem['NAME'];
}
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

$arDatasetOption = [];
$arNmarketIblocksNeeds = \Fgsoft\Nmarket\Options\ModuleIblockList::getList();

foreach ($arNmarketIblocksNeeds as $key => $arItem) {
    $arDatasetOption[] = [
        $key,
        $arItem['TITLE'],
        '',
        ['selectbox', $arIblocks]
    ];
}

$arTabs = [
    [
        'DIV' => 'settings',
        'TAB' => Loc::getMessage('FGSOFT_NMARKET_SETTINGS'),
        'TITLE' => Loc::getMessage('FGSOFT_NMARKET_SETTINGS_TITLE'),
        'OPTIONS' => [
            [
                'feedUrl',
                Loc::getMessage('FGSOFT_NMARKET_FEED_URL'),
                \Bitrix\Main\Application::getDocumentRoot(),
                ['text']
            ],
             [
                'feedUploadPath',
                Loc::getMessage('FGSOFT_NMARKET_UPLOAD_PATH'),
                '/nmarket/xml/',
                ['text']
            ],

        ]
    ],
    [
        'DIV' => 'dataset',
        'TAB' => Loc::getMessage('FGSOFT_NMARKET_DATASET_SETTINGS'),
        'TITLE' => Loc::getMessage('FGSOFT_NMARKET_DATASET_SETTINGS_TITLE'),
        'OPTIONS' => $arDatasetOption

    ],
];


if (check_bitrix_sessid() && $request->get('Update')) {
    //Сохраняем справочники в Options
    //и добавляем их в таблицу
    foreach ($request->getPostList() as $nodeName => $IBLOCK_ID) {
        foreach ($arTabs as $arTab) {
            if (!empty($arTab['OPTIONS'])) {
                __AdmSettingsSaveOptions($module_id, $arTab['OPTIONS']);
            }
        }

    }
}
$tabControl = new CAdminTabControl('tabControl', $arTabs);

if ($exception = $APPLICATION->GetException()) {
    echo \CAdminMessage::ShowMessage(array(
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => $exception->GetString(),
        'HTML' => true,
        'TYPE' => 'ERROR',

    ));
}
$tabControl->Begin(); ?>
<form name="fgsoft_nmarket" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request->get('mid'))?>&amp;lang=<?=$request->get('lang');?>">
    <?php
        foreach ($arTabs as $tab) {
            $tabControl->BeginNextTab();
            if ($tab['OPTIONS']) {
                __AdmSettingsDrawList($module_id, $tab['OPTIONS']);
            }
        }
    ?>
    <?= bitrix_sessid_post(); ?>
    <?php $tabControl->Buttons(); ?>
    <input type="submit" name="Update" value="Сохранить">

</form>
<?php $tabControl->End();