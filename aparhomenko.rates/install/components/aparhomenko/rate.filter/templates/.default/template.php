<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<div class="rate_filter">
    <h3>Фильтр курсов валют</h3>
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:main.ui.filter",
        "",
        array(
            "FILTER_ID" => $arResult["GRID_ID"],
            "GRID_ID" => $arResult["GRID_ID"],
            'FILTER' => $arResult["FIELDS"],
            "ENABLE_LIVE_SEARCH" => false,
            "ENABLE_LABEL" => true,
            "COMPONENT_TEMPLATE" => "",
        ),
        false
    );
    ?>
</div>