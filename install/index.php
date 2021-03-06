<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class FGSOFT_NMARKET extends \CModule
{
    public static $STATIC_MODULE_ID = 'fgsoft.nmarket';

    public static $MODELS = [
        '\Fgsoft\Nmarket\Model\FlatParamTable',
        '\Fgsoft\Nmarket\Model\FilesTable'
    ];

    public function __construct()
    {
        $arModuleVersion = array();
        require $this->getPath() . '/install/version.php';
        
        $this->MODULE_NAME = Loc::getMessage('FGSOFT_NMARKET_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('FGSOFT_NMARKET_MODULE_DESCRIPTION');
        
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        
        $this->MODULE_ID = self::$STATIC_MODULE_ID;
        
        $this->MODULE_SORT = 10000;
        
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        
        $this->PARTNER_NAME = Loc::getMessage('FGSOFT_NMARKET_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('FGSOFT_NMARKET_PARTNER_URI');
    }

    public function DoInstall()
    {
        global $APPLICATION;
        
        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallDB();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('FGSOFT_NMARKET_D7_NOT_FIND'));
        }

        if (!CopyDirFiles($this->getPath() . '/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/', true, true)) {
            $APPLICATION->ThrowException(Loc::getMessage('FGSOFT_NMARKET_DONT_COPY_FILES'));
        }

        $this->registerAgents();

        $APPLICATION->IncludeAdminFile(Loc::getMessage('FGSOFT_NMARKET_INSTALL_SUCCESS_TITLE'), $this->getPath() . '/install/step.php');
        
    }
    
    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);
        $this->unregisterAgents();

        $this->UnInstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallDB()
    {
        global $APPLICATION;

        Loader::includeModule($this->MODULE_ID);

        foreach (self::$MODELS as $modelClass) {
            if (!\Bitrix\Main\Application::getConnection((Fgsoft\Nmarket\Model\FlatParamTable::getConnectionName()))->isTableExists(
                \Bitrix\Main\Entity\Base::getInstance($modelClass)->getDBTableName()
            )){
                \Bitrix\Main\Entity\Base::getInstance($modelClass)->createDbTable();
            }
        }

    }

    public function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

//        foreach (self::$arOrmEntity as $entity) {
//            \Bitrix\Main\Application::getConnection($entity::getConnectionName())
//                ->queryExecute('drop table if exists ' . \Bitrix\Main\Entity\Base::getInstance($entity)->getDBTableName());
//        }
    }

    private function registerAgents()
    {
        $date = new DateTime();
        $date->modify('+1 day');
        $date->setTime(6, '01', '00');

        $nextDate =  FormatDateFromDB(date('d:m:Y h:m:s', $date->getTimestamp()), 'FULL');

        \CAgent::Add([
            'NAME' => '\Fgsoft\Nmarket\Agents\ProcessLoad::start();',
            'MODULE_ID' => $this->MODULE_ID,
            'PERIOD' => 'N',
            'INTERVAL' => '86400',
            'DATECHECK' => $nextDate,
            'ACTIVE' => 'Y',

            'NEXT_EXEC' => $nextDate
        ]);
    }

    private function unregisterAgents()
    {
        \CAgent::RemoveAgent(
            '\Fgsoft\Nmarket\Agents\ProcessLoad::start();',
            $this->MODULE_ID
        );
    }

    private function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }
    
    public function getPath($getRootPath = false)
    {
        if ($getRootPath) {
            return str_ireplace(\Bitrix\Main\Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }
}