<?php

namespace AppBundle\DBAL;

/**
 * In case you need to add new value or remove old value from enum field using migrations:diff
 * simply remove comment for according enum field and then run migrations:diff (on local PC)
 */
class UserGenderEnum extends EnumType
{
    const VALUE_MALE= 'Men';
    const VALUE_FEMALE = 'Women';

    // @see http://symfony.com/doc/current/reference/forms/types/choice.html#example-usage
    protected static $valuesNames = [
        'Men' => self::VALUE_MALE,
        'Women' => self::VALUE_FEMALE,
    ];

    // add config to /app/config/config.yml > doctrine > dbal > types
    protected static $name = 'user_gender_enum';

    // in case it can be NULL set true
    protected static $nullable = true;
}
