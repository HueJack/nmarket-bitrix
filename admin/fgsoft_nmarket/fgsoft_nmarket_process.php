<?php
/**
 * User: Nikolay Mesherinov
 * Date: 01.09.17
 * Time: 15:17
 */
$start = microtime(true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");

$module_id = 'fgsoft.nmarket';
\Bitrix\Main\Loader::includeModule($module_id);

$pathTo = \COption::GetOptionString($module_id, 'feedUploadPath');
$pathTo = \Bitrix\Main\Application::getDocumentRoot() . '/upload/' . $pathTo . '/feed.xml';

///Скачиваем файл
$arFile = \CFile::MakeFileArray($path);
$io = \CBXVirtualIo::GetInstance();
if (!$io->DirectoryExists($pathTo)) {
    $io->CreateDirectory($pathTo);
}
copy($arFile['tmp_name'], $pathTo);

//Загружаем xml в систему
if ($io->FileExists($pathTo)) {
    $xmlReader = new \XMLReader();

    $xmlReader->open($pathTo);

    \Fgsoft\Nmarket\Facade\FacadeProcessing::process($xmlReader);

    require_once 'after_loader.php';
} else {
    echo 'Не найден файл выгрузки' . $pathTo;
    die();
}

echo date('s', microtime(true) - $start);

?>

<?php require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
