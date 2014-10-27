<?php
/**
 * To get environment variables
 */
/**
 * environment class
 */
class Environment
{
    protected static $config = null;

    /**
     * is CLI mode
     *
     * @return bool
     */
    public static function isCli()
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * get runtime config
     *
     * @return object
     */
    public static function create()
    {
        self::$config = (true === isset(self::$config) ? self::$config : Config::env());
        $config = self::$config;

        // get config
        if (true === property_exists(self::$config, Url::host())) {
            $config = self::$config->{Url::host()};
        }
        else {
            $config = (false === empty(self::$config->default) ? self::$config->default : null);
        }

        if (false === isset($config)) {
            $ex = new ApplicationException(Config::exception()->application->ex1003);
            throw $ex;
        }

        return $config;
    }

    /**
     * debug - debug
     *
     * @param mixed $var - variable
     *
     * @return null
     */
    public static function debug($var)
    {
        $config = Environment::create();

        if (true === $config->debug) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }

    /**
     * displayError
     *
     * @param Exception $ex - exception
     *
     * @return null
     */
    public static function displayError($ex)
    {
        $wrapper_style = array(
            'border: 1px solid #ccc',
            'box-shadow: 0 30px 100px 5px rgba(0, 0, 0, .5)',
            'word-break: break-all',
            'padding: 5px;',
            'word-wrap: break-word',
        );
        $header_style = array(
            'font-size: 22px',
        );
        $pre_style = array(
            'padding: 5px;',
            'word-break: break-all',
            'word-wrap: break-word',
        );

        HttpResponse::html(sprintf(
            '<div style="%s">
                <h1 style="%s">%s:</h1>
                <pre style="%s"><code>%s</code></pre>
            </div>',
            implode(';', $wrapper_style),
            implode(';', $header_style),
            ucfirst($ex->getLevel()),
            implode(';', $pre_style),
            $ex->getMessages()
        ));
    }
}
?>