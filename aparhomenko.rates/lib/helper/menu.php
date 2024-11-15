<?php

namespace Aparhomenko\Rates\Helper;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Menu
{
    /** Полученива массива меню модуля
     * @param $arGlobalMenu
     * @param $arModuleMenu
     * @return void
     */
    public static function getAdminMenu(&$arGlobalMenu, &$arModuleMenu)
    {
        global $APPLICATION;
        $moduleId = "aparhomenko.rates";

        if (Loader::includeModule($moduleId)) {
            if ($APPLICATION->GetGroupRight($moduleId) != 'D') {

                if (!isset($arGlobalMenu['global_menu_aparhomenko'])) {
                    $arGlobalMenu['global_menu_aparhomenko'] = [
                        'menu_id' => 'aparhomenko',
                        'text' => Loc::getMessage('APARHOMENKO_RATE_GLOBAL_MENU'),
                        'title' => Loc::getMessage('APARHOMENKO_RATE_GLOBAL_MENU'),
                        'sort' => 0,
                        'items_id' => 'global_menu_aparhomenko_items',
                        "icon" => "",
                        "page_icon" => "",
                    ];
                }

                $menu = [
                    'section' => $moduleId,
                    'sort' => 850,
                    'text' => Loc::getMessage('APARHOMENKO_RATE_MENU_TEXT'),
                    'title' => Loc::getMessage('APARHOMENKO_RATE_MENU_TITLE'),
                    'items_id' => 'aparhomenko.rates',
                    'items' => [
                        'rate_list' => [
                            'text' => Loc::getMessage('APARHOMENKO_RATE_MENU_AUTH_LIST_RATES'),
                            'url' => 'aparhomenko.rates_rate_list.php?lang=' . LANGUAGE_ID,
                            'title' => Loc::getMessage('APARHOMENKO_RATE_MENU_AUTH_LIST_RATES'),
                            'more_url' => [
                                'aparhomenko.rate_list.php'
                            ],
                        ],
                        'settings' => [
                            'text' => Loc::getMessage('APARHOMENKO_RATE_MENU_ALL_SETTINGS_TITLE'),
                            'url' => 'aparhomenko.rates_settings.php?lang=' . LANGUAGE_ID,
                            'title' => Loc::getMessage('APARHOMENKO_RATE_MENU_ALL_SETTINGS_TITLE'),
                        ],

                    ]
                ];
            }

            $arGlobalMenu['global_menu_aparhomenko']['items'][$moduleId] = $menu;
        }
    }
}