<?php
/**
 * validator for enum
 */

namespace Ageis;

/**
 * class validator-enum
 */
final class ValidatorEnum
{
    /**
     * valid - valid
     *
     * @param string $value - value
     * @param array  $range - range
     *
     * @return int
     */
    public static function valid($value, $enum)
    {
        $exception = Config::exception();
        return (true === in_array($value, $enum) ? 0 : $exception->datatype->ex2003->code);
    }
}
?>