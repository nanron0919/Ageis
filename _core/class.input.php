<?php
/**
 * class input
 */

/**
 * class input
 */
class Input
{
    const HTTP_GET    = 'get';
    const HTTP_POST   = 'post';
    const HTTP_COOKIE = 'cookie';

    /**
     * fetch get parameter
     *
     * @param string $name   - request name
     * @param bool   $escape - escape
     *
     * @return string
     **/
    public static function get($name, $escape = true)
    {
        return self::_getRequest(self::HTTP_GET, $name, $escape);
    }

    /**
     * fetch post parameter
     *
     * @param string $name   - request name
     * @param bool   $escape - escape
     *
     * @return string
     **/
    public static function post($name, $escape = true)
    {
        return self::_getRequest(self::HTTP_POST, $name, $escape);
    }

    /**
     * fetch cookie parameter
     *
     * @param string $name   - request name
     * @param bool   $escape - escape
     *
     * @return string
     **/
    public static function cookie($name, $escape = true)
    {
        return self::_getRequest(self::HTTP_COOKIE, $name, $escape);
    }

    /**
     * fetch request parameter
     *
     * @param string $method - http method
     * @param string $name   - request name
     * @param bool   $escape - escape
     *
     * @return mixed - as same as the passing parameter
     **/
    private static function _getRequest($method, $name, $escape = true) {
        $escape = (true === is_bool($escape) ? $escape : true);

        switch ($method) {
            case (self::HTTP_GET):
                $var = (false === empty($_GET[$name]) ? $_GET[$name] : '');
                break;

            case (self::HTTP_POST):
                $var = (false === empty($_POST[$name]) ? $_POST[$name] : '');
                break;

            case (self::HTTP_COOKIE):
                $var = (false === empty($_COOKIE[$name]) ? $_COOKIE[$name] : '');
                break;

            default:
                $var = '';
                break;
        }


        if (true === $escape) {
            self::escape($val);
        }

        return $var;
    }

    /**
     * escape html special chars
     *
     * @param mixed $arg - arg
     *
     * @return null
     */
    public static function escape(&$arg)
    {
        if (true === is_array($arg)) {
            foreach ($arg as &$val) {
                self::escape($val);
            }
        }
        else {
            $arg = htmlspecialchars($arg);
        }
    }
}
?>