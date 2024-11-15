<?php

namespace Aparhomenko\Rates;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use SimpleXMLElement;
use SoapClient;


Loc::loadMessages(__FILE__);

class Import
{
    /** Получение курсов валют  с удаленного сервера
     * @return array
     **/
    public static function getRatesFromRemote()
    {
        $wsdl = 'https://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';
        $res = [];
        $cbr = new SoapClient($wsdl, array('soap_version' => SOAP_1_2, 'exceptions' => true));
        $date = $cbr->GetLatestDateTime();
        $result = $cbr->GetCursOnDateXML(array('On_date' => $date->GetLatestDateTimeResult));

        $allowedParam = Option::get("aparhomenko.rates", "field_list");
        $allowed = [];
        if ($allowedParam) {
            $allowed = explode(",", $allowedParam);
        }

        if ($result->GetCursOnDateXMLResult->any) {
            $xml = new SimpleXMLElement($result->GetCursOnDateXMLResult->any);
            foreach ($xml->ValuteCursOnDate as $currency) {
                $date = new Type\Date($date->GetLatestDateTimeResult, 'Y-m-d\TH:i:s.uO');
                $code = $currency->VchCode->__toString();
                if (in_array($code, $allowed)) {

                    $res[] = [
                        "DATE" => $date,
                        "CODE" => $code,
                        "COURSE" => floatval($currency->Vcurs),
                    ];
                }
            }
        }
        return $res;

    }

    /** Получение списка валют  с удаленного сервера
     * @return array
     **/
    public static function getCurListFromRemote()
    {
        $wsdl = 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';
        $res = [];
        $cbr = new SoapClient($wsdl, array('soap_version' => SOAP_1_2, 'exceptions' => true));

        $result = $cbr->EnumValutes();
        if ($result->EnumValutesResult->any) {
            $xml = new SimpleXMLElement($result->EnumValutesResult->any);
            foreach ($xml->ValuteData->EnumValutes as $currency) {
                $res[$currency->VcharCode->__toString()] = $currency->VcharCode->__toString();
            }
        }
        return $res;
    }

    /**  Функция для агента загрузки курсов
     * @return string
     */
    public static function updateRatesAgent()
    {
        $items = self::getRatesFromRemote();
        self::updateRates($items);
        return "\Aparhomenko\Rates\Import::updateRatesAgent();";

    }

    /** Обновление или добавление курса валют
     * @param $items - список элементов
     * @return void
     */
    public static function updateRates($items)
    {
        foreach ($items as $courseItem) {
            if ($courseItem["COURSE"] > 0 && !empty($courseItem["CODE"])) {
                $rateDb = RateTable::getList([
                    "filter" => [
                        "CODE" => $courseItem["CODE"],
                        "DATE" => $courseItem["DATE"],
                    ],
                    "select" => ["ID"],
                ]);

                if ($rateItem = $rateDb->fetch()) {
                    RateTable::update($rateItem["ID"], [
                        "COURSE" => $courseItem["COURSE"]
                    ]);
                } else {
                    $res = RateTable::add([
                        "CODE" => $courseItem["CODE"],
                        "DATE" => $courseItem["DATE"],
                        "COURSE" => $courseItem["COURSE"]
                    ]);
                    var_dump($res);

                }
            }

        }
    }

}