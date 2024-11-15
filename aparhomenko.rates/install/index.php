<?php

use Aparhomenko\Rates\RateTable;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config as Conf;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class aparhomenko_rates extends CModule
{
    var $exclusionAdminFiles;
    const MODULE_ID = 'aparhomenko.rates';

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . "/version.php");

        $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        );

        $this->MODULE_ID = self::MODULE_ID;
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("APARHOMENKO_RATES_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("APARHOMENKO_RATES_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("APARHOMENKO_RATES_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("APARHOMENKO_RATES_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    /** Получить папку модуля
     * @param $notDocumentRoot
     * @return array|string|string[]
     */
    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    /** Провека версии ядра
     * @return mixed
     */
    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    /**  Добавление таблиц в БД
     * @return void
     */
    function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(RateTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aparhomenko\Rates\RateTable')->getDBTableName()
        )
        ) {
            Base::getInstance('\Aparhomenko\Rates\RateTable')->createDbTable();
        }

        Option::set($this->MODULE_ID, "field_list", "USD");
    }

    /** Удаление таблиц в БД
     * @return void
     */
    function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(RateTable::getConnectionName())->
        queryExecute('drop table if exists ' . Base::getInstance('\Aparhomenko\Rates\\RateTable')->getDBTableName());

        Option::delete($this->MODULE_ID);
    }

    /** Регистрация обработчиков на события
     * @return void
     */
    function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('main', 'OnBuildGlobalMenu', $this->MODULE_ID, '\Aparhomenko\Rates\Event', 'onBuildGlobalMenuHandler');
    }

    /** Удаление обработчиков на события
     * @return void
     */
    function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnBuildGlobalMenu', $this->MODULE_ID, '\Aparhomenko\Rates\Event', 'onBuildGlobalMenuHandler');
    }

    /** Копирование файлов
     * @param $arParams
     * @return bool
     */
    function InstallFiles($arParams = array())
    {
        $path = $this->GetPath() . "/install/components";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path))
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
        else
            throw new InvalidPathException($path);

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            CopyDirFiles($this->GetPath() . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"); //если есть файлы для копирования
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }

        return true;
    }

    /** Удаление файлов
     * @return bool
     */
    function UnInstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/aparhomenko/');

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->GetPath() . '/install/admin/', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }
        return true;
    }

    /** Добавление агентов
     * @return void
     */
    function InstallAgents()
    {
        CAgent::AddAgent("\Aparhomenko\Rates\Import::updateRatesAgent();", $this->MODULE_ID, "N", 86400);
    }

    /** Удаление агентов
     * @return void
     */
    function UnInstallAgents()
    {
        CAgent::RemoveAgent("\Aparhomenko\Rates\Import::updateRatesAgent();");
    }

    function DoInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
            $this->InstallAgents();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("APARHOMENKO_RATES_INSTALL_ERROR_VERSION"));
        }
        $APPLICATION->IncludeAdminFile(Loc::getMessage("APARHOMENKO_RATES_INSTALL_TITLE"), $this->GetPath() . "/install/step.php");
    }

    function DoUninstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if ($request["step"] < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("APARHOMENKO_RATES_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep1.php");
        } elseif ($request["step"] == 2) {
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->UnInstallAgents();

            if ($request["savedata"] != "Y")
                $this->UnInstallDB();

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage("APARHOMENKO_RATES_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep2.php");
        }
    }
}