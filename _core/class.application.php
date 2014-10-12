<?php
/**
 * application
 */

/**
 * application
 */
final class Application
{
    public $route;
    public static $config;

    /**
     * constructor
     */
    public function __construct()
    {
        // session_start
        Session::start();

        $this->route = new Route;

        // setting up application environment
        $this->settingUp();
    }


    /**
     * run - run application
     *
     * @return null
     */
    public function run()
    {
        $route = $this->route->findMatchRoute();

        if (false === empty($route) && false === empty($route->controller)) {
            $controller = Loader::loadController($route->controller);

            try {
                $controller->run($route);
            }
            catch (Exception $e) {
                if (true === $this->config->debug) {
                    self::debug($e);
                }
                else {
                    Http::redirect($this->config->error->e50x);
                }
            }

        }
        else {
            Http::redirect($this->config->error->e404);
        }
    }

    /**
     * loadRoute - load route
     *
     * @return null
     */
    public function loadRoute()
    {
        $this->route->findMatchRoute();
    }

    /**
     * setting up application running environment
     *
     * @param string $hostname - host name
     *
     * @return null
     */
    public static function settingUp($hostname = '')
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
            $ex = Config::exception()->application->ex1003;
            throw new ApplicationException($ex);
        }

        self::$config = $config;
    }

    /**
     * getEnv - get environment
     *
     * @return string - enviroment
     */
    public static function getEnv()
    {
        return self::$config->env;
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
        $config = Config::env();

        if (true === $config->debug) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }

}
?>