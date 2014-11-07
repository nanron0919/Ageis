<?php
/**
 * validator for url
 */

namespace Ageis;

/**
 * class validator-url
 */
final class ValidatorUrl
{
    /**
     * valid - valid
     *
     * @param int   $value - value
     *
     * @return int
     */
    public static function valid($value)
    {
        $pattern  = '/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/iS';
        $exception = Config::exception();

        return (1 === preg_match($pattern, $value) ? 0 : $exception->datatype->ex2001->code);
    }
}
?>