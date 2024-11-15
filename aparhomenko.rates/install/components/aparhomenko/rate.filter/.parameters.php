<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Aparhomenko\Rates\RateTable;
use Bitrix\Main\Localization\Loc;

Bitrix\Main\Loader::includeModule("aparhomenko.rates");

Loc::loadMessages(__FILE__);

$map = RateTable::getMap();
$arProperty = [];
foreach ($map as $id => $mapItem) {
    $arProperty[$id] = Loc::getMessage("APARHOMENKO_RATE_" . $id);
}
$arComponentParameters = array(
    "PARAMETERS" => array(
        'FIELDS' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("APARHOMENKO_ASSOC_PROPERTY"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arProperty,
            "ADDITIONAL_VALUES" => "N",
            "DEFAULT" => array_keys($arProperty),
        ),
    )
);
