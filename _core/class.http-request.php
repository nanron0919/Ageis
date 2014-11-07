<?php
/**
 * class HttpResponse
 */

namespace Ageis;

/**
 * class HttpResponse
 */
class HttpRequest
{
    const HTTP_GET    = 'get';
    const HTTP_POST   = 'post';
    const HTTP_COOKIE = 'cookie';

    /**
     * __callStatic - fetch http request parameter
     *
     * @param string $name      - request name
     * @param array  $arguments - arguments
     *
     * @return string
     */
    public static function __callStatic($name, $arguments)
    {
        $var_name = (string)(false === empty($arguments[0]) ? $arguments[0] : '');
        $escape   = (bool)(false === empty($arguments[1]) ? $arguments[1] : true);

        switch ($name) {
            case (self::HTTP_GET):
                $var = (false === empty($_GET[$var_name]) ? $_GET[$var_name] : '');
                break;

            case (self::HTTP_POST):
                $var = (false === empty($_POST[$var_name]) ? $_POST[$var_name] : '');
                break;

            case (self::HTTP_COOKIE):
                $var = (false === empty($_COOKIE[$var_name]) ? $_COOKIE[$var_name] : '');
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
     * escape - escape html special chars
     *
     * @param mixed &$arg - arg
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