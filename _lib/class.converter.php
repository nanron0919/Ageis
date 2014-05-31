<?php
/**
 * class converter
 */

/**
 * class converter
 */
class Converter
{
    /**
     * arrayToObject - array to object
     *
     * @param mixed $val - val
     *
     * @return object
     */
    public static function arrayToObject($val)
    {
        return json_decode(json_encode($val));
    }

    /**
     * objectToArray - object to array
     *
     * @param mixed $val - val
     *
     * @return array
     */
    public static function objectToArray($val)
    {
        return json_decode(json_encode($val), true);
    }
}

?>