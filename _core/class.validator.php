<?php
/**
 * class validator
 */

namespace Ageis;

/**
 * class validator
 */
class Validator
{
    const FIELD_VALUE  = 0;
    const FIELD_PARAMS = 1;

    const RANGE_MAX  = 0;
    const RANGE_MIN  = 1;

    protected static $types = array(
        // basic type
        'int'       => array('is_int', array('Ageis\ValidatorNumber', 'valid')),
        'float'     => array('is_float', array('Ageis\ValidatorNumber', 'valid')),
        'double'    => array('is_double', array('Ageis\ValidatorNumber', 'valid')),
        'string'    => array('is_string', array('Ageis\ValidatorString', 'valid')),
        'bool'      => array('is_bool', null),
        'object'    => array('is_object', null),
        'array'     => array('is_array', null),
        'null'      => array('is_null', null),
        // special format
        'enum'      => array('is_string', array('Ageis\ValidatorEnum', 'valid')),
        'url'       => array('is_string', array('Ageis\ValidatorUrl', 'valid')),
        'date'      => array('is_string', array('Ageis\ValidatorDate', 'valid')),
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
        $value  = (true === isset($arguments[self::FIELD_VALUE])  ? $arguments[self::FIELD_VALUE]  : null);
        $params = (true === isset($arguments[self::FIELD_PARAMS]) ? $arguments[self::FIELD_PARAMS] : null);

        $result = 0;
        $define_types = array_keys(self::$types);

        if (true === in_array($type, $define_types)) {
            $func = self::$types[$type][0];
            $validator = self::$types[$type][1];

            // check basic type is ok
            $result = call_user_func_array($func, array($value));
            $result = (true === $result ? 0 : $exception->datatype->ex2001->code);

            // check advance
            if (0 === $result && null !== $validator) {
                $result = call_user_func_array($validator, array($value, $params));
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