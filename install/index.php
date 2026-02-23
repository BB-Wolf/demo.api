<?php
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Localization\Loc;

class demo_api extends CModule
{
    var $MODULE_ID = "demo.api";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__ . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = Loc::getMessage("DEMO_API_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("DEMO_API_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("DEMO_API_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("DEMO_API_PARTNER_URI");
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (CheckVersion(\Bitrix\Main\ModuleManager::getVersion("main"), "14.00.00")) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->registerRouting();
            RegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, '\Demo\Api\Classes\Handlers\Main', 'onPageStartHandler');
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("DEMO_API_INSTALL_ERROR_VERSION"));
        }
    }

    public function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->unRegisterRouting();
        UnRegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, '\Demo\Api\Classes\Handlers\Main', 'onPageStartHandler');
    }

    private function registerRouting()
    {
        $configuration = \Bitrix\Main\Config\Configuration::getInstance();
        $routing = $configuration->get('routing');

        if (!is_array($routing)) {
            $routing = ['value' => ['config' => []]];
        }

        if (!isset($routing['value']) || !is_array($routing['value'])) {
            $routing['value'] = ['config' => []];
        }

        if (!isset($routing['value']['config']) || !is_array($routing['value']['config'])) {
            $routing['value']['config'] = [];
        }

        if (!in_array('api.php', $routing['value']['config'])) {
            $routing['value']['config'][] = 'api.php';
            $configuration->add('routing', $routing);
            $configuration->saveConfiguration();
        }
    }

    private function unRegisterRouting()
    {
        $configuration = \Bitrix\Main\Config\Configuration::getInstance();
        $routing = $configuration->get('routing');
        if ($routing && isset($routing['value']['config']) && is_array($routing['value']['config'])) {
            $key = array_search('api.php', $routing['value']['config']);
            if ($key !== false) {
                unset($routing['value']['config'][$key]);
                $routing['value']['config'] = array_values($routing['value']['config']);
                $configuration->add('routing', $routing);
                $configuration->saveConfiguration();
            }
        }
    }
}
