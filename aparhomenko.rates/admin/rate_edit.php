<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Loader;
use Bitrix\Main\Type;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Aparhomenko\Rates\RateTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

const MODULE_ID = 'aparhomenko.rates';

if (!Loader::includeModule(MODULE_ID)) {
    die();
}

$arRequestValues = Application::getInstance()->getContext()->getRequest()->getValues();
if (!$arRequestValues) {
    die();
}

$ID = (int)$arRequestValues["ID"];

$POST_RIGHT = $APPLICATION->GetGroupRight(MODULE_ID);
if ($POST_RIGHT === "D") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);

$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("APARHOMENKO_EDIT_TAB_MAIN"),
        "ICON" => "main_user_edit",
        "TITLE" => Loc::getMessage("APARHOMENKO_EDIT_TAB_MAIN"),
    ]
];

$tabControl = new CAdminForm("aparhomenko_rate_edit", $aTabs);

/****************
 * Check data
 ****************/
if ($ID > 0) {
    $rateRes = RateTable::getById($ID);
    $rate = $rateRes->fetch();
    if (empty($rate)) {
        LocalRedirect("/bitrix/admin/aparhomenko.rates_rate_list.php?lang=" . LANG);
    }
}
if (!empty($arRequestValues["CODE"])) {
    $rate["CODE"] = $arRequestValues["CODE"];
}
if (!empty($arRequestValues["DATE"])) {
    $rate["DATE"] = new Type\Date($arRequestValues["DATE"], 'd.m.Y');
}
if (!empty($arRequestValues["COURSE"])) {
    $rate["COURSE"] = $arRequestValues["COURSE"];
}
$res = true;
/****************
 * Action
 ****************/
// Delete
if ($arRequestValues['action'] === 'delete' && $ID > 0) {

    $result = RateTable::delete($ID);
    if ($result->isSuccess()) {
        LocalRedirect("/bitrix/admin/aparhomenko.rates_rate_list.php?lang=" . LANG);
    }
}

// POST
if ($REQUEST_METHOD === "POST" && ($save != "" || $apply != "") && $POST_RIGHT === "W" && check_bitrix_sessid()) {
    $bVarsFromForm = true;

    if (empty($errors)) {
        // update rate
        if ($ID > 0) {
            try {
                $result = RateTable::update($ID, $rate);
                if (!$result->isSuccess()) {
                    $errors = $result->getErrorMessages();
                    $res = false;
                } else {
                    $res = true;
                }
            } catch (SqlQueryException $e) {
                $errors = [$e->getMessage()];
            }

        } else {// insert rate
            try {
                $result = RateTable::add($rate);
                if ($result->isSuccess()) {
                    $ID = $result->getId();
                    $res = true;
                } else {
                    $errors = $result->getErrorMessages();
                    $res = false;
                }
            } catch (SqlQueryException $e) {
                $errors = [$e->getMessage()];
            }
        }
    }

} else {
    $res = false;
}

if ($res) {
    if ($apply != "") {
        LocalRedirect(
            "/bitrix/admin/aparhomenko.rates_rate_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&"
            . $tabControl->ActiveTabParam()
        );
    } else {
        LocalRedirect("aparhomenko.rates_rate_list.php?lang=" . LANG);
    }
}

$APPLICATION->SetTitle(
    ($ID > 0
        ? Loc::getMessage("APARHOMENKO_EDIT_EDIT") . $ID
        : Loc::getMessage(
            "APARHOMENKO_EDIT_ADD"
        ))
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

/****************
 * Header menu
 ****************/
// back
$aMenu[] = [
    "TEXT" => Loc::getMessage("APARHOMENKO_EDIT_BACK"),
    "TITLE" => Loc::getMessage("APARHOMENKO_EDIT_BACK"),
    "ICON" => "btn_list",
    "LINK" => "aparhomenko.rates_rate_list.php?lang=" . LANG,
];
if ($ID > 0) {
    $aMenu[] = [
        "SEPARATOR" => "Y",
    ];
    // del
    $aMenu[] = [
        "TEXT" => Loc::getMessage("APARHOMENKO_EDIT_DEL"),
        "TITLE" => Loc::getMessage("APARHOMENKO_EDIT_DEL"),
        "LINK" => "javascript:if(confirm('" . Loc::getMessage("APARHOMENKO_EDIT_DEL_CONF")
            . "'))window.location='aparhomenko.rates_rate_edit.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&"
            . bitrix_sessid_get() . "';",
        "ICON" => "btn_delete",
    ];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

/****************
 * Errors
 ****************/
if (isset($errors) && is_array($errors) && count($errors) > 0) {
    CAdminMessage::ShowMessage(
        [
            "MESSAGE" => $errors[0],
        ]
    );
}
if ($arRequestValues["mess"] === "ok" && $ID > 0) {
    CAdminMessage::ShowMessage(
        [
            "MESSAGE" => Loc::getMessage("APARHOMENKO_EDIT_SAVED"),
            "TYPE" => "OK",
        ]
    );
}

/****************
 * Tabs
 ****************/
$tabControl->BeginEpilogContent();
?>

<?= bitrix_sessid_post() ?>

    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
<?php $tabControl->EndEpilogContent();


$tabControl->Begin(["FORM_ACTION" => $APPLICATION->GetCurUri()]);
$tabControl->BeginNextFormTab();

// Tab1
if ($ID) {
    $tabControl->AddViewField('ID', Loc::getMessage("APARHOMENKO_EDIT_ID"), $ID, false);
}
$tabControl->AddCalendarField(
    'DATE',
    Loc::getMessage("APARHOMENKO_EDIT_DATE"),
    $rate['DATE']
);
$tabControl->AddEditField(
    'CODE',
    Loc::getMessage("APARHOMENKO_EDIT_CODE"),
    false,
    ["size" => 3, "maxlength" => 3],
    $rate['CODE']
);
$tabControl->AddEditField(
    'COURSE',
    Loc::getMessage("APARHOMENKO_EDIT_COURSE"),
    false,
    ["size" => 30, "maxlength" => 255],
    ($rate['COURSE'] === 0 || $rate['COURSE']) ? (int)$rate['COURSE'] : null
);
$tabControl->BeginNextFormTab();

/****************
 * Footer buttons
 ****************/

$tabControl->Buttons([
    'disabled' => false,
    'back_url' => 'aparhomenko.rates_rate_list.php?lang=' . LANGUAGE_ID
]);

$tabControl->Show();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");