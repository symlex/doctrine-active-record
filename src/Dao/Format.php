<?php

namespace Doctrine\ActiveRecord\Dao;

use DateTime;
use Doctrine\ActiveRecord\Exception\FormatException;

/**
 * Utility class to convert various data types from and to SQL format
 *
 * Since SQL is a standard, for performance reasons and to enforce code portability,
 * this class intentionally is static. It's a pure conversion tool and does not
 * contain any state.
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
class Format
{
    const NONE = '';
    const INT = 'int';
    const FLOAT = 'float';
    const STRING = 'string';
    const ALPHANUMERIC = 'alphanumeric';
    const SERIALIZED = 'serialized';
    const JSON = 'json';
    const CSV = 'csv';
    const BOOL = 'bool';
    const TIME = 'H:i:s';
    const TIMEU = 'H:i:s.u'; // Support for microseconds (up to six digits)
    const TIMETZ = 'H:i:sO'; // Support for timezone (e.g. "+0230")
    const TIMEUTZ = 'H:i:s.uO'; // Support for microseconds & timezone
    const DATE = 'Y-m-d';
    const DATETIME = 'Y-m-d H:i:s';
    const DATETIMEU = 'Y-m-d H:i:s.u'; // Support for microseconds (up to six digits)
    const DATETIMETZ = 'Y-m-d H:i:sO'; // Support for timezone (e.g. "+0230")
    const DATETIMEUTZ = 'Y-m-d H:i:s.uO'; // Support for microseconds & timezone
    const TIMESTAMP = 'U';

    /**
     * Converts data from sql data source
     *
     * @param string $format
     * @param mixed $data
     * @throws FormatException
     * @return mixed
     */
    public static function fromSql(string $format, $data = null)
    {
        if ($data === null) {
            return null;
        }

        switch ($format) {
            case self::NONE:
                return $data;
            case self::DATE:
                $result = DateTime::createFromFormat($format, $data);
                $result->setTime(0, 0, 0);
                return $result;
            case self::TIMESTAMP:
            case self::TIMETZ:
            case self::TIMEUTZ:
            case self::DATETIMETZ:
            case self::DATETIMEUTZ:
                // Format must match - no auto detection
                return DateTime::createFromFormat($format, $data);
            case self::TIME:
            case self::TIMEU:
                // Includes microseconds?
                $timeFormat = (strlen($data) > 8) ? self::TIMEU : self::TIME;
                return DateTime::createFromFormat($timeFormat, $data);
            case self::DATETIME:
            case self::DATETIMEU:
                // Includes microseconds?
                $dateTimeFormat = (strlen($data) > 19) ? self::DATETIMEU : self::DATETIME;
                return DateTime::createFromFormat($dateTimeFormat, $data);
            case self::INT:
                return (integer)$data;
            case self::BOOL:
                return (bool)$data;
            case self::FLOAT:
                return floatval($data);
            case self::STRING:
                return (string)$data;
            case self::ALPHANUMERIC:
                return preg_replace('/[^a-zA-Z0-9_ ]/', '', $data);
            case self::SERIALIZED:
                return unserialize($data);
            case self::JSON:
                return json_decode($data, true);
            case self::CSV:
                return str_getcsv($data);
            default:
                throw new FormatException ('Unknown format: ' . $format);
        }
    }

    /**
     * Converts data to sql format
     *
     * @param string $format
     * @param mixed $data
     * @throws FormatException
     * @return mixed
     */
    public static function toSql(string $format, $data = null)
    {
        if ($data === null) {
            return null;
        }

        switch ($format) {
            case self::NONE:
                return $data;
            case self::TIME:
            case self::TIMEU:
            case self::TIMETZ:
            case self::TIMEUTZ:
            case self::DATE:
            case self::DATETIME:
            case self::DATETIMEU:
            case self::DATETIMETZ:
            case self::DATETIMEUTZ:
            case self::TIMESTAMP:
                if (empty($data)) {
                    $result = null;
                } elseif (!is_object($data)) {
                    $datetime = new DateTime($data);
                    $result = $datetime->format($format);
                } elseif ($data instanceof DateTime) {
                    $result = $data->format($format);
                } else {
                    throw new FormatException('Unknown datetime object: ' . get_class($data));
                }

                return $result;
            case self::INT:
                return (integer)$data;
            case self::BOOL:
                return (integer)$data;
            case self::FLOAT:
                if (strpos($data, ',') > strpos($data, '.')) {
                    $data = str_replace(array('.', ','), array('', '.'), $data);
                } elseif(strpos($data, '.') > strpos($data, ',')) {
                    $data = str_replace(',', '', $data);
                }

                return floatval($data);
            case self::STRING:
                return (string)$data;
            case self::ALPHANUMERIC:
                return preg_replace('/[^a-zA-Z0-9_ ]/', '', $data);
            case self::SERIALIZED:
                return serialize($data);
            case self::JSON:
                return json_encode($data);
            case self::CSV:
                return implode(',', $data);
            default:
                throw new FormatException ('Unknown format: ' . $format);
        }
    }
}