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

    /**
     * constructor
     */
    public function __construct()
    {
        // session_start
        Session::start();

        $this->route = new Route;
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
                // TODO: do something.
                var_dump($e);
            }

        }
        else {
            echo 'redirect';
            // TODO: not really does redirect yet.
            // Http::redirect('http://google.com');
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
