<?php

use Aparhomenko\Rates\RateTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\Application;
use Bitrix\Main\Context;

class RateFilter extends CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        $this->checkModules();

        $this->errorCollection = new Main\ErrorCollection();
        $this->itemsCount = (int)$params['PER_PAGE'] ?: 10;
        $this->gridId = 'RATE_LIST';

        $this->arFields = $this->arParams['FIELDS'];
        if (empty($this->arFields)) {
            $map = RateTable::getMap();
            unset($map["ID"]);
            $this->arFields = $map;
        }

        return $params;
    }

    public function executeComponent()
    {
        $filterFields = [];
        foreach ($this->arFields as $code => $field) {

            switch ($field["data_type"]) {
                case "datetime":
                    $type = "date";
                    break;
                case "float":
                case "integer":
                    $type = "number";
                    break;
                default:
                    $type = 'string';
            }
            $curList = self::getCurrencyList();
            if ($code == "CODE") {
                $filterFields[] = ["id" => $code, "type" => "list", 'items' => $curList, "default" => true, "name" => Loc::getMessage("APARHOMENKO_RATE_" . $code)];
            } else {
                $filterFields[] = ["id" => $code, "type" => $type, "default" => true, "name" => Loc::getMessage("APARHOMENKO_RATE_" . $code)];
            }
        }
        $this->arResult["FIELDS"] = $filterFields;
        $this->arResult["FILTER_ID"] = $this->arParams['FILTER_NAME'] ?: "arFilter";
        $this->arResult["GRID_ID"] = $this->gridId;

        $this->includeComponentTemplate();
    }

    public static function getCurrencyList()
    {
        $dbResult = RateTable::getList([
            'select' => ["CODE"],
            "cache" => ["ttl" => 3600]
        ]);
        $res = [];
        while ($item = $dbResult->fetch()) {
            $res[$item["CODE"]] = $item["CODE"];
        }
        return $res;
    }

    protected function checkModules()
    {
        if (!Main\Loader::includeModule('aparhomenko.rates'))
            throw new Main\LoaderException(Loc::getMessage('APARHOMENKO_RATES_MODULE_NOT_INSTALLED'));
    }
}