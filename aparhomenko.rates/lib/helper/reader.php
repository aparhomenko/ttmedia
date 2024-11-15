<?php

namespace Aparhomenko\Rates\Helper;

use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\XML;
use Bitrix\Main\Type;
use CDataXML;


Loc::loadMessages(__FILE__);

class Reader
{
    /** Чтение файла xml в массив
     * @param $filePath
     * @return array
     */
    public static function readXml($filePath)
    {
        $rates = [];
        if ($data = File::getFileContents($filePath)) {
            $xml = new CDataXML();

            $xml->LoadString($data);

            $nodes = $xml->SelectNodes('/currencies')->__toArray();
            foreach ($nodes['#']["currency"] as $node) {
                $item = $node["#"];
                $date = new Type\Date($item["date"][0]["#"], 'd.m.Y');

                $rates[] = array(
                    'DATE' => $date,
                    'CODE' => $item["code"][0]["#"],
                    'COURSE' => $item["course"][0]["#"],
                );
            }
        }
        return $rates;
    }
}
