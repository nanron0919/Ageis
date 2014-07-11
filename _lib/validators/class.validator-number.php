<?php
/**
 * validator for number
 */

/**
 * class validator-number
 */
final class ValidatorNumber
{
    /**
     * valid - valid
     *
     * @param int   $value - value
     * @param array $range - range
     *
     * @return int
     */
    public static function valid($value, $range)
    {
        $exception = Config::exception();
        $max    = (true === isset($range[Validator::RANGE_MAX]) ? $range[Validator::RANGE_MAX] : PHP_INT_MAX);
        $min    = (true === isset($range[Validator::RANGE_MIN]) ? $range[Validator::RANGE_MIN] : -PHP_INT_MAX);

        return ($value >= $min && $value <= $max ? 0 : $exception->datatype->ex2002->code);
    }
}
?>