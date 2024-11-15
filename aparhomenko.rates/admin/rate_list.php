<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use  Aparhomenko\Rates\RateTable;
use Bitrix\Main\Application;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = "aparhomenko.rates";
Loader::includeModule($moduleId);

Loc::loadMessages(__FILE__);

$postRight = $APPLICATION->GetGroupRight($moduleId);
if ($postRight == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$arRequestValues = Application::getInstance()->getContext()->getRequest()->getValues();
$ID = (int)$arRequestValues["ID"];

// Delete
if ($arRequestValues['action_button'] === 'delete' && $ID > 0) {

    $result = RateTable::delete($ID);
}

$tableName = RateTable::getTableName();
$oSort = new CAdminSorting($tableName, "ID", "desc");
$lAdmin = new CAdminList($tableName, $oSort);

$arrHeaders = [
    [
        "id" => "ID",
        "content" => Loc::getMessage("APARHOMENKO_RATE_ID"),
        "sort" => "ID",
        "align" => "right",
        "default" => true,
    ],
    [
        "id" => "CODE",
        "content" => Loc::getMessage("APARHOMENKO_RATE_CODE"),
        "sort" => "CODE",
        "default" => true,
    ],
    [
        "id" => "DATE",
        "content" => Loc::getMessage("APARHOMENKO_RATE_DATE"),
        "sort" => "DATE",
        "default" => true,
    ],
    [
        "id" => "COURSE",
        "content" => Loc::getMessage("APARHOMENKO_RATE_COURSE"),
        "sort" => "COURSE",
        "default" => true,
    ],
];
$by = $arRequestValues["by"] ?: "ID";
$order = $arRequestValues["order"] ?: "asc";
$arResult = RateTable::getList([
    "order" => [$by => $order]
])->fetchAll();

/**
 * Create table
 */
$rs = new CDBResult;
$rs->InitFromArray($arResult);
$rsData = new CAdminResult($rs, $tableName);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("APARHOMENKO_RATE_NAV")));
$lAdmin->AddHeaders($arrHeaders);

while ($arRes = $rsData->NavNext(true, "f_")) {
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $row->AddViewField("ID", '<a href="aparhomenko.rates_rate_edit.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_ID . '</a>');

    // Actions
    $arActions = [];
    $arActions[] = [
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => Loc::getMessage("APARHOMENKO_RATE_EDIT"),
        "ACTION" => $lAdmin->ActionRedirect("aparhomenko.rates_rate_edit.php?ID=" . $f_ID),
    ];
    if ($postRight >= "W") {
        $arActions[] = [
            "ICON" => "delete",
            "TEXT" => Loc::getMessage("APARHOMENKO_RATE_DEL"),
            "ACTION" => "if(confirm('" . GetMessage('APARHOMENKO_RATE_DEL_CONF') . "')) " . $lAdmin->ActionDoGroup(
                    $f_ID,
                    "delete"
                ),
        ];
    }

    $arActions[] = ["SEPARATOR" => true];
    if (is_set($arActions[count($arActions) - 1], "SEPARATOR")) {
        unset($arActions[count($arActions) - 1]);
    }
    $row->AddActions($arActions);

}

$aContext = [
    [
        "TEXT" => Loc::getMessage("APARHOMENKO_RATE_POST_ADD_TEXT"),
        "LINK" => "aparhomenko.rates_rate_edit.php?lang=" . LANG,
        "TITLE" => Loc::getMessage("APARHOMENKO_RATE_POST_ADD_TITLE"),
        "ICON" => "btn_new",
    ],
    [
        "TEXT" => Loc::getMessage("APARHOMENKO_RATE_POST_UPLOAD_TEXT"),
        "LINK" => "aparhomenko.rates_rate_upload.php?lang=" . LANG,
        "TITLE" => Loc::getMessage("APARHOMENKO_RATE_POST_UPLOAD_TEXT"),
        "ICON" => "btn_new",
    ],
];

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("APARHOMENKO_RATE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");