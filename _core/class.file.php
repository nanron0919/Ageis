<?php
/**
 * File class
 */

namespace Ageis;

/**
 * File class
 */
class File
{
    /**
     * mkdir - makes directory
     *
     * @param string $path - path
     * @param int    $mode - mode
     *
     * @return bool
     */
    public static function mkdir($path, $mode = 0774)
    {
        $result = true;

        if (false === is_dir($path)) {
            $result = mkdir($path, $mode, true);
        }

        return $result;
    }

    /**
     * read - read file
     *
     * @param string $filename  - file name
     * @param bool   $is_binary - is binary
     *
     * @return mixed
     */
    public static function read($filename, $is_binary = false)
    {
        $contents = '';
        $mode     = (true === (bool)$is_binary ? 'rb' : 'r');

        if (true === is_file($filename) && true === is_readable($filename)) {
            $handle = fopen($filename, $mode);
            $contents = fread($handle, filesize($filename));
            fclose($handle);
        }
        else {
            throw new ApplicationException(Config::exception()->application->ex1001);
        }

        return $contents;
    }

    /**
     * write - write file
     *
     * @param string $filename - file name (relative path that is under the root)
     * @param string $content  - content
     * @param string $mode     - mode
     *
     * @return bool
     */
    public static function write($filename, $content, $mode = 'a')
    {
        $result = false;
        $filename = APP_ROOT . '/' . $filename;
        $path = dirname($filename);

        if (true === self::mkdir($path) && false === is_file($filename)) {
            $result = touch($filename);
        }

        if (true === is_writable($filename)) {
            $handle = fopen($filename, $mode);
            $contents = fwrite($handle, $content . "\n");
            fclose($handle);
        }
        else {
            throw new ApplicationException(Config::exception()->application->ex1001);
        }

        return $result;
    }

    /**
     * move - move file
     *
     * @param string $old_name - old name
     * @param string $new_name - new name
     *
     * @return bool
     */
    public static function move($old_name, $new_name)
    {
        $result = self::mkdir(dirname($new_name));

        if (true === is_readable($old_name) && true === $result) {
            $result = rename($old_name, $new_name);
        }
        else {
            throw new ApplicationException(Config::exception()->application->ex1001);
        }

        return $result;
    }

    /**
     * delete - delete
     *
     * @param string $filename - old name
     *
     * @return bool
     */
    public static function delete($filename)
    {
        $result = false;

        if (true === file_exists($filename)) {
            $result = unlink($filename);
        }

        return $result;
    }

    /**
     * glob - glob
     *
     * @param string $filename - old name
     *
     * @return array
     */
    public static function glob($filename)
    {
        return glob($filename);
    }
}
?>