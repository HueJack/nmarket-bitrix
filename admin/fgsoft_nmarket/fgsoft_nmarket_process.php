<?php
/**
 * User: Nikolay Mesherinov
 * Date: 01.09.17
 * Time: 15:17
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");

$module_id = 'fgsoft.esbn';
\Bitrix\Main\Loader::includeModule($module_id);

$obConfig = new Fgsoft\Esbn\Config();
$arInsertSettings = $obConfig->getConfig();

$obErrors = new Fgsoft\Esbn\Errors\ErrorsCollection();

$pathToUpload = \COption::GetOptionString($module_id, 'uploadPath');
try {
    $xmlFileReader = new \Fgsoft\Esbn\Reader\XMLFileReader(
        ['pathname' => $pathToUpload . '/xml/esbn/Feed_238_demo.xml']
    );

    foreach ($arInsertSettings as $key => $setting) {
        $obGetter = Fgsoft\Esbn\Getter\GetterFabric::getGetter(
            $setting,
            $xmlFileReader

        );
        $obSaver = new \Fgsoft\Esbn\Saver\IblockElementSaver($obGetter, $obErrors);
        $obSaver->save();
    }
} catch (\Exception $e) {
    $obErrors->addOne(new \Fgsoft\Esbn\Errors\Error($e->getMessage(), 'EsbnBootstrapError'));
}

/**
 * Если в процессе были ошибки добавим их в лог и выведем в админку предупреждение
 */
if ($obErrors->hasErrors()) {
    foreach ($obErrors as $error) {
        \Bitrix\Main\Diag\Debug::writeToFile($error->getMessage(), $error->getCode(), $errorLog);
    }

    CAdminNotify::Add([
        'MESSAGE' => 'В процессе выгрузки произошли ошибки! Лог файл доступен по адресу ' . $errorLog,
        'TYPE' => CAdminNotify::TYPE_ERROR
    ]);
}

require_once 'after_loader.php';

?>

<?php require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
