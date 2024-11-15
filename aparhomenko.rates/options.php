<?php

use Aparhomenko\Rates\Import;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

const MODULE_ID = 'aparhomenko.rates';

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight(MODULE_ID) < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::includeModule(MODULE_ID);

$request = HttpApplication::getInstance()->getContext()->getRequest();
$codeList = Import::getCurListFromRemote();

$aTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('APARHOMENKO_RATES_TAB_SETTINGS'),
        'OPTIONS' => array(
            array('field_list', Loc::getMessage('APARHOMENKO_FIELD_CUR_LIST'),
                '',
                array('multiselectbox', $codeList)),
        ),
    )
);

if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) {
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption))
                continue;

            $optionName = $arOption[0];
            $optionValue = $request->getPost($optionName);
            Option::set(MODULE_ID, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
        }
    }
}

#Визуальный вывод
$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
<?php $tabControl->Begin(); ?>
<form method='post'
      action='<?php echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request['mid']) ?>&amp;lang=<?= $request['lang'] ?>'
      name='APARHOMENKO_settings'>

    <?php foreach ($aTabs as $aTab):
        if ($aTab['OPTIONS']):?>
            <?php $tabControl->BeginNextTab(); ?>
            <?php __AdmSettingsDrawList(MODULE_ID, $aTab['OPTIONS']); ?>

        <?php endif;
    endforeach;
    $tabControl->BeginNextTab();
    $tabControl->Buttons(); ?>

    <input type="submit" name="Update" value="<?php echo GetMessage('MAIN_SAVE') ?>">
    <input type="reset" name="reset" value="<?php echo GetMessage('MAIN_RESET') ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<?php $tabControl->End(); ?>

