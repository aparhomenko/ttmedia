<?php

namespace Aparhomenko\Rates;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;

class RateTable extends Entity\DataManager
{
    /**
     * Имя таблицы курсов валют
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'rate_ap';
    }

    /**
     * Список полей таблицы курсов валют
     *
     * @return array[]
     */
    public static function getMap()
    {
        return array(
            'ID' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true
            ],
            'CODE' => [
                'data_type' => 'string',
                'required' => true,
                'size' => 3,
            ],
            'DATE' => [
                'data_type' => 'datetime',
                'default_value' => new Type\DateTime(),
            ],
            'COURSE' => [
                'data_type' => 'float',
                'required' => true,
            ]
        );

    }
}