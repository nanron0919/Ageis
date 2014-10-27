<?php
/**
 * To get environment variables
 */
/**
 * environment class
 */
class Environment
{
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
    public static function getConfig()
    {
        $config = Config::env();

        // get config
        if (true === property_exists($config, Url::host())) {
            $config = $config->{Url::host()};
        }
        else {
            $config = (false === empty($config->default) ? $config->default : null);
        }

        if (true === empty($config)) {
            $ex = new ApplicationException(Config::exception()->application->ex1003);
            self::displayError($ex);
            throw $ex;
        }

        return $config;
    }
}
?>