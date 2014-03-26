<?php
/**
 * File class
 */

/**
 * File class
 */
class File
{
    /**
     * mkdir
     *
     * @param string $path - path
     *
     * @return bool
     */
    public static function mkdir($path)
    {
        if (false === is_dir($path)) {
            return mkdir($path, 0777, true);
        }
        else {
            return true;
        }
    }
}
?>