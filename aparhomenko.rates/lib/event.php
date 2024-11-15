<?php

namespace Aparhomenko\Rates;

use Aparhomenko\Rates\Helper\Menu;

class event
{
    /**
     * Обработчик события вывода меню в админке
     *
     * @param $arGlobalMenu
     * @param $arModuleMenu
     * @return void
     */
    public static function onBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu)
    {
        Menu::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }
}