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
        self::$config = Environment::getConfig();
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
            if (true === self::$config->debug) {
                self::displayError($ex);
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

    /**
     * getEnv - get environment
     *
     * @return string - enviroment
     */
    public static function getEnv()
    {
        return (true === isset(self::$config->env) ? self::$config->env : 'development');
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

    /**
     * displayError
     *
     * @param Exception $ex - exception
     *
     * @return null
     */
    protected static function displayError($ex)
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