<?php
/**
 * Class Session
 */

/**
 * Class Session
 */
final class Session
{
    /**
     * start - start session
     *
     * @return null
     */
    public static function start()
    {
        self::_setting();
        session_start();
    }

    /**
     * add - add session param
     *
     * @param string $name - name
     * @param string $val  - val
     *
     * @return null
     */
    public static function add($name, $val)
    {
        $_SESSION[$name] = $val;
    }

    /**
     * get - get session param
     *
     * @param string $name - name
     *
     * @return mixed
     */
    public static function get($name)
    {
        return (false === empty($_SESSION[$name]) ? $_SESSION[$name] : '');
    }

    /**
     * remove - remove session param
     *
     * @param string $name - name
     *
     * @return null
     */
    public static function remove($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * clearAll - clear all session param
     *
     * @param string $name - name
     *
     * @return null
     */
    public static function clearAll()
    {
        foreach ($_SESSION as &$val) {
            unset($val);
        }
    }

    /////////////////////
    // private methods //
    /////////////////////

    /**
     * _setting - setting params
     *
     * @return null
     */
    private static function _setting()
    {
        $config = Config::session();

        if (false === is_dir($config->files)) {
            mkdir($config->files);
        }

        session_set_cookie_params($config->lifetime, $config->files, $config->path, $config->secure);
    }
}
?>