<?php

namespace AppBundle\DBAL;

/**
 * In case you need to add new value or remove old value from enum field using migrations:diff
 * simply remove comment for according enum field and then run migrations:diff (on local PC)
 */
class MessageStatusEnum extends EnumType
{
    const VALUE_SENDING= 'Sending';
    const VALUE_SEND = 'Send';
    const VALUE_SEEN = 'Seen';

    // @see http://symfony.com/doc/current/reference/forms/types/choice.html#example-usage
    protected static $valuesNames = [
        'Sending' => self::VALUE_SENDING,
        'Send' => self::VALUE_SEND,
        'Seen' => self::VALUE_SEEN,
    ];

    // add config to /app/config/config.yml > doctrine > dbal > types
    protected static $name = 'message_status_enum';

    // in case it can be NULL set true
    protected static $nullable = true;
}
