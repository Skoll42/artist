<?php

namespace AppBundle\DBAL;

/**
 * In case you need to add new value or remove old value from enum field using migrations:diff
 * simply remove comment for according enum field and then run migrations:diff (on local PC)
 */
class ChargingStatusEnum extends EnumType
{
    const VALUE_PENDING = 'pending';
    const VALUE_RESERVED = 'reserved';
    const VALUE_CANCELED = 'canceled';
    const VALUE_CHARGING = 'charging';
    const VALUE_REFUNDED = 'refunded';

    // @see http://symfony.com/doc/current/reference/forms/types/choice.html#example-usage
    protected static $valuesNames = [
        'pending' => self::VALUE_PENDING,
        'reserved' => self::VALUE_RESERVED,
        'canceled' => self::VALUE_CANCELED,
        'charging' => self::VALUE_CHARGING,
        'refunded' => self::VALUE_REFUNDED,
    ];

    // add config to /app/config/config.yml > doctrine > dbal > types
    protected static $name = 'charging_status_enum';

    // in case it can be NULL set true
    protected static $nullable = false;
}
