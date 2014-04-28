<?php
/**
 * class config
 */

/**
 * class config
 */
class Config
{
    private static $_plus_setting = array();

    /**
     * call static
     *
     * @param string $name      - name
     * @param array  $arguments - arguments
     *
     * @return object
     */
    public static function __callStatic($name, $arguments)
    {
        $env = (false === empty($arguments[0]) ? $arguments[0] : '');
        $filename = sprintf('%s/_configs/config.%s.php', APP_ROOT, $name);
        $config = array();

        if (true === file_exists($filename)) {
            $config = require $filename;
        }

        // fetch for a specific root
        if (false === empty($config[$env])) {
            $config = $config[$env];
        }

        if (false === empty(self::$_plus_setting[$name])) {
            $config = array_merge($config, self::$_plus_setting[$name]);
        }

        return Converter::arrayToObject($config);
    }

    /**
     * add more variable to config setting
     *
     * @param string $name - namespace
     * @param string $key  - key
     * @param string $val  - val
     *
     * @return null
     */
    public static function add($name, $key, $val)
    {
        self::$_plus_setting[$name][$key] = $val;
    }
}
?>