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
    public $config;

    /**
     * constructor
     */
    public function __construct()
    {
        // session_start
        Session::start();

        $this->route = new Route;
        $this->config = Config::env(self::getEnv());
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
     * getEnv - get environment
     *
     * @return string - enviroment
     */
    public static function getEnv()
    {
        return Config::env()->env;
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
