<?php
/**
 * validator for date
 */

namespace Ageis;

/**
 * class validator-date
 */
final class ValidatorDate
{
    /**
     * valid - valid
     *
     * @param string $value - value
     *
     * @return int
     */
    public static function valid($value)
    {
        $exception = Config::exception();
        return (
            1 === preg_match('/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/', $value)
            ? 0
            : $exception->datatype->ex2001->code
        );
    }
}
?>