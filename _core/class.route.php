<?php
/**
 * class Route
 */

/**
 * class Route
 */
class Route
{
    private $_routes = array();
    private $_uri    = '';

    /**
     * constructor
     */
    public function __construct()
    {
        $this->_routes = Config::route();

        // remove query string from uri
        $url = Url::path();

        $this->_uri = str_replace('-', '_', $url);

        $this->replaceRoutePattern();
    }

    /**
     * load route
     *
     * @return array - route
     */
    public function findMatchRoute()
    {
        $_route = array(
            'controller' => ''
        );

        foreach ($this->_routes as $name => &$route) {
            $is_match = $this->isMatch($this->_uri, $route->regex, $matches);

            if (true === $is_match) {
                $this->bindParams($route, $matches);
                $_route = $route;
                break;
            }
        }

        return $_route;
    }

    /**
     * check whether is match
     *
     * @param string $uri     - uri
     * @param string $pattern - regex pattern
     * @param array  $matches - &matches
     *
     * @return bool
     */
    public function isMatch($uri, $pattern, &$matches = array())
    {
        $match = preg_match_all($pattern, $uri, $matches);

        return !empty($match);
    }

    /**
     * load route
     *
     * @return null
     */
    public function replaceRoutePattern()
    {
        foreach ($this->_routes as $name => &$route) {
            $route->params = array(
                'route_name' => $name,
                'controller' => $route->controller,
            );

            if (false === empty($route->pattern)) {
                // TODO: need to take time to refactoring as soon as possible!!
                $route->regex = preg_replace('/(?P<l_bracket>\()?(?P<slash>\/)?\:(?P<group>\w+)(?P<r_bracket>\))?/i', '$2$1?P<$3>\w+$4', $route->pattern);
                $route->regex = str_replace('/(', '\/?(', $route->regex);
                $route->regex = '/^' . $name . '\/' . str_replace(')', ')?', $route->regex) . '/';
                $route->regex = preg_replace('|(\/)([?]P<\w+>.*)(\\\/[?])|', '$1($2)$3', $route->regex);

                preg_match_all('/\:\w+/', $route->pattern, $matches);

                foreach ($matches[0] as $val) {
                    $val = str_replace(':', '', $val);
                    $route->params[$val] = '';
                }
            }
            else {
                $route->regex = '/^$/';
            }

        }
    }

    /**
     * bind params
     *
     * @param array  $route   - &route
     * @param array  $matches - matches
     *
     * @return null
     */
    public function bindParams(&$route, $matches)
    {
        foreach ($route->params as $name => &$val) {
            $val = (false === empty($matches[$name]) ? explode('/', $matches[$name][0]) : array($val));
            $val = strtolower($val[0]);

            if ('method' === $name) {
                $parts = explode('_', $val);
                $method = '';

                foreach ($parts as $part) {
                    $method .= ucfirst($part);
                }

                $val = lcfirst($method);
            }
        }
    }

}
?>