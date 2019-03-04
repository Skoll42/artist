<?php

namespace AppBundle\DBAL;

/**
 * In case you need to add new value or remove old value from enum field using migrations:diff
 * simply remove comment for according enum field and then run migrations:diff (on local PC)
 */
class BookingStatusEnum extends EnumType
{
    const VALUE_PENDING = 'pending';
    const VALUE_CONFIRMED = 'confirmed';
    const VALUE_REJECTED = 'rejected';
    const VALUE_CANCELED = 'canceled';
    const VALUE_DISPUTED = 'disputed';
    const VALUE_ARCHIVED = 'archived';

    // @see http://symfony.com/doc/current/reference/forms/types/choice.html#example-usage
    protected static $valuesNames = [
        'pending' => self::VALUE_PENDING,
        'confirmed' => self::VALUE_CONFIRMED,
        'rejected' => self::VALUE_REJECTED,
        'canceled' => self::VALUE_CANCELED,
        'archived' => self::VALUE_ARCHIVED,
        'disputed' => self::VALUE_DISPUTED,
    ];

    // add config to /app/config/config.yml > doctrine > dbal > types
    protected static $name = 'booking_status_enum';

    // in case it can be NULL set true
    protected static $nullable = false;
}
