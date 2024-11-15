<?php

use Aparhomenko\Rates\RateTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\Application;
use Bitrix\Main\UI\PageNavigation;


class RateList extends CBitrixComponent
{

    /**
     * Проверка наличия необходимых модулей
     *
     * @return void
     */
    protected function checkModules()
    {
        if (!Main\Loader::includeModule('aparhomenko.rates'))
            throw new Main\LoaderException(Loc::getMessage('APARHOMENKO_RATES_MODULE_NOT_INSTALLED'));
    }

    public function onPrepareComponentParams($params)
    {
        $this->checkModules();

        $this->errorCollection = new Main\ErrorCollection();
        $this->itemsCount = (int)$params['PER_PAGE'] ?: 10;

        $this->gridId = 'RATE_LIST';
        $this->arFields = $params['FIELDS'];
        if (empty($this->arFields)) {
            $map = RateTable::getMap();
            $this->arFields = array_keys($map);
        }

        return $params;
    }

    public function executeComponent()
    {
        $arRequestValues = Application::getInstance()->getContext()->getRequest()->getValues();

        $filterOption = new Bitrix\Main\UI\Filter\Options($this->gridId);
        $filterData = $filterOption->getFilter([]);
        $arrFilter = [];

        foreach ($filterData as $key => $value) {
            $cleanKey = str_replace("_to", "", $key);
            $cleanKey = str_replace("_from", "", $cleanKey);
            if (in_array($cleanKey, $this->arFields)) {

                if (strpos($key, "_to") !== false) {
                    $arrFilter["<=" . $cleanKey] = $value;

                } else if (strpos($key, "_from") !== false) {
                    $arrFilter[">=" . $cleanKey] = $value;

                } else {
                    $arrFilter[$key] = $value;
                }
            }
        }

        $nav = new PageNavigation("nav-r");
        $nav->allowAllRecords(true)
            ->setPageSize($this->itemsCount)
            ->initFromUri();


        $by = $arRequestValues["by"] ?: "ID";
        $order = $arRequestValues["order"] ?: "asc";

        $dbResult = RateTable::getList([
            'select' => $this->arFields,
            'filter' => $arrFilter,
            'order' => array($by => $order),
            "offset" => $nav->getOffset(),
            "limit" => $nav->getLimit(),
            "count_total" => true,
            "cache" => ["ttl" => 3600],
        ]);
        $totalCount = $dbResult->getCount();
        $nav->setRecordCount($totalCount);

        $items = [];
        while ($rateItem = $dbResult->fetch()) {
            $rateItem["DATE"] = $rateItem["DATE"]->toString();
            $items[] = [
                "data" => $rateItem,
            ];
        }

        $headers = [];
        foreach ($this->arFields as $code) {
            $headers[] = ["id" => $code, "default" => true, "name" => Loc::getMessage("APARHOMENKO_RATE_" . $code)];
        }
        $this->arResult["ITEMS"] = $items;
        $this->arResult["HEADERS"] = $headers;
        $this->arResult["NAV"] = $nav;
        $this->arResult["ON_PAGES_COUNT"] = $this->itemsCount;
        $this->arResult["GRID_ID"] = $this->gridId;
        $this->arResult["TOTAL_ROWS_COUNT"] = $totalCount;
        $this->arResult["SHOW_PAGINATION"] = $totalCount>$this->itemsCount;

        $this->includeComponentTemplate();


    }


}

