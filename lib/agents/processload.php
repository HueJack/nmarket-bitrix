<?php
/**
 * User: Nikolay Mesherinov
 * Date: 16.11.2017
 * Time: 14:33
 */

namespace Fgsoft\Nmarket\Agents;

use Bitrix\Main\Diag\Debug;
use Fgsoft\Nmarket\Facade\FacadeProcessing;

class ProcessLoad
{
    public function start()
    {
        if (false == ($pathTo = self::downloadFile())) {
            \CAdminNotify::Add([
                'MESSAGE' => 'Error!',
                'TYPE' => \CAdminNotify::TYPE_Error
            ]);
            return false;
        }

        Debug::dumpToFile(['loadFile' => $pathTo], 'upload/error.txt');
        try {
        $xmlReader = new \XMLReader();
        $xmlReader->open($pathTo);
            FacadeProcessing::process($xmlReader, true);
        $xmlReader->open($pathTo);
            FacadeProcessing::process($xmlReader, false);
        } catch (\Exception $e) {
            \CAdminNotify::Add([
                'MESSAGE' => $e->getMessage(),
                'TYPE' => \CAdminNotify::TYPE_ERROR
            ]);
        }

        \CAdminNotify::Add([
            'MESSAGE' => 'Ok!',
            'TYPE' => \CAdminNotify::TYPE_NORMAL
        ]);

        return 'Fgsoft\Nmarket\Agents\ProcessLoad::start();';
    }

    public static function downloadFile()
    {
        $module_id = 'fgsoft.nmarket';

        \Bitrix\Main\Loader::includeModule($module_id);

        $pathTo = \COption::GetOptionString($module_id, 'feedUploadPath', 'nmarket');
        $path = \COption::GetOptionString($module_id, 'feedUrl', null);
        $fileName = 'feed.xml';


        if ($path == null) {
            \CAdminNotify::Add([
                'MESSAGE' => 'Ошибка! Не задан URL к файлу выгрузки NMarket!',
                'TYPE' => \CAdminNotify::TYPE_ERROR
            ]);

            return false;
        }

        $pathTo = \Bitrix\Main\Application::getDocumentRoot() . '/upload/' . trim($pathTo, '\/') . '/';
        $filePath = $pathTo . $fileName;

        ///Скачиваем файл
        $arFile = \CFile::MakeFileArray($path);

        $io = \CBXVirtualIo::GetInstance();
        if (!$io->DirectoryExists($pathTo)) {
            $io->CreateDirectory($pathTo);
        }

        if (!copy($arFile['tmp_name'], $filePath)) {
            \CAdminNotify::Add([
                'MESSAGE' => 'Не удалось скопировать файл выгрузки( ' . $arFile['tmp_name'] . ') по пути ' . $filePath,
                'TYPE' => \CAdminNotify::TYPE_ERROR
            ]);

            return false;
        }

        if ($io->FileExists($filePath)) {
            return $filePath;
        }

        return false;
    }
}