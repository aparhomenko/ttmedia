<?php

use Bitrix\Main\Config\Option;
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

$arRequestFile = Application::getInstance()->getContext()->getRequest()->getFile("file");
$path = str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));

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
 * Action
 ****************/
// POST
if ($REQUEST_METHOD === "POST" && ($save != "" || $apply != "") && $POST_RIGHT === "W" && check_bitrix_sessid()) {
    $res = true;

    $bVarsFromForm = true;

    if (empty($errors)) {
        // upload rate

        $fileID = CFile::SaveFile($arRequestFile, "main");
        if (isset($fileID) && !empty($fileID))
            $filePath = $_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($fileID);

        if (file_exists($filePath) === false) {
            $error[] = [
                'TYPE' => 'File',
                'msg' => 'File does not exist'
            ];
        }

        if (empty($error) && strpos($filePath, '.xml') !== false) {

            $dataXml = Aparhomenko\Rates\Helper\Reader::readXml($filePath);
            Aparhomenko\Rates\Import::updateRates($dataXml);

        }
        CFile::Delete($filePath);
    }

} else {
    $res = false;
}

if ($res) {
    if ($apply != "") {
        LocalRedirect(
            "/bitrix/admin/aparhomenko.rates_rate_list.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&"
            . $tabControl->ActiveTabParam()
        );
    } else {
        LocalRedirect("aparhomenko.rates_rate_list.php?lang=" . LANG);
    }
}

$APPLICATION->SetTitle(Loc::getMessage("APARHOMENKO_EDIT_TAB_MAIN"));
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
$tabControl->AddFileField(
    "file",
    Loc::getMessage("APARHOMENKO_FILE"),
    "",
    []
);
$tabControl->BeginCustomField("TEMPLATE", "");
?>
    <tr>
        <td width="40%"></td>
        <td width="60%">
            <a href="<?= $path ?>/template.xml"
               download><?= Loc::getMessage('APARHOMENKO_DOWNLOAD') ?></a>
        </td>
    </tr>
<?php
$tabControl->EndCustomField("TEMPLATE", '');

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