<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js'); ?>
<div style="clear: both">
    <h3>Список курсов валют</h3>
    <?php
    $APPLICATION->IncludeComponent(
        'bitrix:main.ui.grid',
        '',
        array(
            'GRID_ID' => $arResult["GRID_ID"],
            'HEADERS' => $arResult["HEADERS"],
            'ROWS' => $arResult['ITEMS'],
            'AJAX_MODE' => 'Y',

            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "N",
            "AJAX_OPTION_HISTORY" => "N",

            "ALLOW_COLUMNS_SORT" => false,
            "ALLOW_ROWS_SORT" => [],
            "ALLOW_COLUMNS_RESIZE" => false,
            "ALLOW_HORIZONTAL_SCROLL" => false,
            "ALLOW_SORT" => false,
            "ALLOW_PIN_HEADER" => false,
            "ACTION_PANEL" => [],

            "SHOW_CHECK_ALL_CHECKBOXES" => false,
            "SHOW_ROW_CHECKBOXES" => false,
            "SHOW_ROW_ACTIONS_MENU" => false,
            "SHOW_GRID_SETTINGS_MENU" => false,
            "SHOW_NAVIGATION_PANEL" => true,
            "SHOW_PAGINATION" => $arResult['SHOW_PAGINATION'],
            "SHOW_SELECTED_COUNTER" => false,
            "SHOW_TOTAL_COUNTER" => false,
            "SHOW_PAGESIZE" => true,
            "SHOW_ACTION_PANEL" => true,

            "ENABLE_COLLAPSIBLE_ROWS" => false,
            'ALLOW_SAVE_ROWS_STATE' => true,
            "SHOW_MORE_BUTTON" => false,
            'NAV_OBJECT' => $arResult['NAV'],
            "TOTAL_ROWS_COUNT" => $arResult['TOTAL_ROWS_COUNT'],
            "PAGE_SIZES" => $arResult['ON_PAGES_COUNT'],
            "DEFAULT_PAGE_SIZE" => 50
        ),
        false,
        array('HIDE_ICONS' => 'Y')
    );
    ?>
</div>