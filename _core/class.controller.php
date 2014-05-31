<?php
/**
 * base controller
 */

/**
 * base controller
 */
abstract class Controller
{
    const DEFAULT_METHOD = 'index';

    protected $route;
    protected $request_vars = array();
    protected $env;

    /**
     * constructor
     *
     * @param array $app - application
     */
    public function __construct($route)
    {
        $this->route = $route;
        $this->env = Config::env();

        $caching_content = View::getCache(Url::requestUri());

        if (false === empty($caching_content)) {
            HttpResponse::html($caching_content);
            exit;
        }
    }

    /**
     * run - run the specific controller
     *
     * @param string $name - module name
     */
    final public function run()
    {
        $request_vars = array(
            'params' => $this->route->params
        );

        $request_vars = Converter::arrayToObject(array_merge($request_vars, $this->_getRequest()));

        $active_method = (false === empty($this->route->params['method'])
            ? $this->route->params['method']
            : self::DEFAULT_METHOD);

        if (true === method_exists($this, $active_method)) {
            $this->$active_method($request_vars);
        }
        else {
            $this->index($request_vars);
        }
    }

    //////////////////////
    // abstract methods //
    //////////////////////
    abstract public function index($args);

    /////////////////////
    // private methods //
    /////////////////////

    /**
     * get request parameters
     *
     * @return array - request vars
     */
    private function _getRequest()
    {
        $map = array(
            'get'    => $_GET,
            'post'   => $_POST,
            'cookie' => $_COOKIE
        );
        $request_vars = array();

        foreach ($map as $key => $request) {
            foreach ($request as $name => $val) {
                $request_vars[$key][$name] = call_user_func('HttpRequest::' . $key, $name);
            }
        }

        return $request_vars;
    }
}
?>
