<?php

namespace AppBundle\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html
 * @see https://github.com/fre5h/DoctrineEnumBundle
 * 
 * If you need to add new value or remove old value from enum field using migrations:diff
 * simply remove comment for according enum field and then run migrations:diff (on local PC)
 */
abstract class EnumType extends Type
{
    protected static $valuesNames = array();
    protected static $name;
    protected static $nullable = false;

    /**
     * It returns array of ENUM values
     * 
     * @return array
     */
    public static function getValues()
    {
        return array_values(static::$valuesNames);
    }

    /**
     * It returns ENUM values names
     * 
     * @return array
     */
    public static function getValuesNames()
    {
        return static::$valuesNames;
    }

    /**
     * It returns ENUM value name by ENUM value
     * 
     * @param string $value
     * @return string|bool
     */
    public static function getValueName($value)
    {
        if (!in_array($value, self::getValues())) {
            return false;
        }
        
        $valuesNames = array_flip(static::$valuesNames);

        return $valuesNames[$value];
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function($val) { return "'".$val."'"; },  self::getValues());

        return "ENUM(".implode(", ", $values).")";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (false == static::$nullable && is_null($value)) {
            throw new \InvalidArgumentException("ENUM field '".static::$name."' can't be null.");
        }
        
        if (!is_null($value) && !in_array($value, self::getValues())) {
            throw new \InvalidArgumentException("Invalid '".static::$name."' value.");
        }
        
        return $value;
    }

    public function getName()
    {
        return static::$name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
