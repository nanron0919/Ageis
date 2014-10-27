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

        // setting up application environment
        $this->config = Environment::create();
    }


    /**
     * run - run application
     *
     * @return null
     */
    public function run()
    {
        $route = $this->route->findMatchRoute();
        $redirect_url = '';
        $ex = null;

        if (false === empty($route) && false === empty($route->controller)) {
            $controller = Loader::loadController($route->controller);

            try {
                $controller->run($route);
            }
            catch (Exception $e) {
                $ex = $e;
                $redirect_url = $this->config->error->e50x;
            }
        }
        else {
            $redirect_url = $this->config->error->e404;
        }

        if (true === isset($ex)) {
            if (true === $this->config->debug) {
                Environment::displayError($ex);
            }
            else {
                Http::redirect($redirect_url);
            }
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
}
?>