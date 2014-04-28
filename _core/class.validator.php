<?php
/**
 * class validator
 */

/**
 * class validator
 */
class Validator
{
    const FIELD_VALUE = 0;
    const FIELD_RANGE = 1;

    const RANGE_MAX  = 0;
    const RANGE_MIN  = 1;

    protected static $types = array(
        'int',
        'float',
        'double',
        'string',
        'bool',
        'enum',
        'object',
        'array',
        'null'
    );

    /**
     * call static
     *
     * @param string $name      - name
     * @param array  $arguments - arguments
     *
     * @return int - 0: success, > 0: error code (refer to config.exception)
     */
    public static function __callStatic($name, $arguments)
    {
        $exception = Config::exception();
        $type   = strtolower(substr($name, 2));
        $value  = (true === isset($arguments[self::FIELD_VALUE]) ? $arguments[self::FIELD_VALUE] : null);
        $range  = (true === isset($arguments[self::FIELD_RANGE]) ? $arguments[self::FIELD_RANGE] : null);

        $result = 0;

        if (true === in_array($type, self::$types)) {
            $func = sprintf('is_%s', $type);

            // check php native function is existing.
            if (true === function_exists($func)) {
                $result = call_user_func_array($func, array($value));
                $result = (true === $result ? 0 : $exception->datatype->ex2001->code);
            }

            switch ($type) {
            case 'string':
                $len = mb_strlen($value);
                $result = (true === self::checkRange($len, $range)
                    ? $result
                    : $exception->datatype->ex2002->code);
                break;

            case 'int':
            case 'float':
            case 'double':
                $result = (true === self::checkRange($value, $range)
                    ? $result
                    : $exception->datatype->ex2002->code);
                break;

            case 'enum':
                $result = (true === self::checkEnum($value, $range)
                    ? $result
                    : $exception->datatype->ex2003->code);
            default:
                break;
            }
        }

        return $result;
    }

    /**
     * checkRange - check range
     *
     * @param int   $value - value
     * @param array $range - range
     *
     * @return bool
     */
    protected static function checkRange($value, $range)
    {
        $max    = (true === isset($range[self::RANGE_MAX]) ? $range[self::RANGE_MAX] : PHP_INT_MAX);
        $min    = (true === isset($range[self::RANGE_MIN]) ? $range[self::RANGE_MIN] : -PHP_INT_MAX);

        return ($value >= $min && $value <= $max);
    }

    /**
     * checkEnum - check enum
     *
     * @param int   $value - value
     * @param array $enum  - enum
     *
     * @return bool
     */
    protected static function checkEnum($value, $enum)
    {
        return in_array($value, $enum);
    }
}

?>