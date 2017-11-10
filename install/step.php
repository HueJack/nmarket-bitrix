<?php
/**
 * Created by PhpStorm.
 * User: huejack
 * Date: 15.06.17
 * Time: 17:54
 */
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

global $APPLICATION;

if ($exception = $APPLICATION->GetException()) {
    echo \CAdminMessage::ShowMessage(array(
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => $exception->GetString(),
        'HTML' => true,
        'TYPE' => 'ERROR',

    ));
} else {
    echo CAdminMessage::ShowNote(Loc::getMessage('MOD_INST_OK'));
}
?>

<form method='get' action="<?=$APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>">
    <input type="submit" name="" value="<?=Loc::getMessage('MOD_BACK');?>">
</form>
