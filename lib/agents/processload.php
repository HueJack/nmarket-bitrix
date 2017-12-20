<?php
/**
 * User: Nikolay Mesherinov
 * Date: 16.11.2017
 * Time: 14:33
 */

namespace Fgsoft\Nmarket\Agents;

use Bitrix\Main\Event;
use Fgsoft\Nmarket\Facade\FacadeProcessing;
use Fgsoft\Nmarket\Log\Log;
use Fgsoft\Nmarket\Log\Logger;

class ProcessLoad
{
    public function start()
    {
        //Удаляем дату следующей проверки DATE_CHECK, иначе через 10 минут запустится 2я выборка
        static::updateDateCheck('fgsoft.nmarket', 'Fgsoft\Nmarket\Agents\ProcessLoad::start();');

        if (false !== ($pathTo = self::downloadFile())) {
            try {
                $xmlReader = new \XMLReader();
                $xmlReader->open($pathTo);
                FacadeProcessing::process($xmlReader, true, Logger::getInstance());

                $xmlReader = new \XMLReader();
                $xmlReader->open($pathTo);
                FacadeProcessing::process($xmlReader, false, Logger::getInstance());
            } catch (\Exception $e) {
                Logger::getInstance()->add(new Log('FATAL', $e->getMessage()));
            }

            Logger::getInstance()->add(new Log('SUCESS', 'Все стадии выгрузки позади'));
        }

        self::sendEvent();

        return 'Fgsoft\Nmarket\Agents\ProcessLoad::start();';
    }

    protected static function updateDateCheck($moduleId, $name)
    {
        $agent = \CAllAgent::GetList(
            [],
            [
                'MODULE_ID' => $moduleId,
                '=NAME' => $name
            ]
        )->Fetch();

        if (!empty($agent['ID'])) {
            $date = new \DateTime('now');
            $date->modify('+1 day');

            \CAllAgent::Update($agent['ID'], ['DATE_CHECK' => $date->format('d.m.Y H:i:s')]);
        }

    }

    public static function downloadFile()
    {
        $module_id = 'fgsoft.nmarket';

        \Bitrix\Main\Loader::includeModule($module_id);

        $pathTo = \COption::GetOptionString($module_id, 'feedUploadPath', 'nmarket');
        $path = \COption::GetOptionString($module_id, 'feedUrl', null);
        $fileName = 'feed.xml';


        if ($path == null) {
            Logger::getInstance()->add(new Log('ERROR_OPTIONS', 'Не задан путь к файлу выгрузки'));

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

        if (!move_uploaded_file($arFile['tmp_name'], $filePath) && !copy($arFile['tmp_name'], $filePath)) {
            Logger::getInstance()->add(new Log('ERROR_DOWNLOAD', 'Не удалось сохранить файл выгрукзи'));
            return false;
        }

        if ($io->FileExists($filePath)) {
            return $filePath;
        }

        return false;
    }

    public static function sendEvent()
    {
        $event = new Event('fgsoft.nmarket', 'onAfterProcess', ['logger' => Logger::getInstance()]);
        $event->send();
    }
}