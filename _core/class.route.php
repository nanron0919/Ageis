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
        $_route = (object) array(
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
                // replace ) as )?
                $route->regex = str_replace(')', ')?', $route->pattern);
                $pattern = '/(([(]?\/?)([:](\w+)))/i';
                preg_match_all($pattern, $route->regex, $matches);
                $strpos = 0;

                foreach ($matches[3] as $index => $match) {
                    $strpos = strpos($route->regex, $match, $strpos);
                    $wrapper = $matches[2][$index];
                    $is_optional = ('(/' === $wrapper);
                    $param_name = $matches[4][$index];

                    $search = $wrapper . $match;

                    if (true === $is_optional) {
                        $replace = sprintf('(\/?(?P<%s>\w+)', $param_name);
                    }
                    else {
                        $replace = sprintf('%s(?P<%s>\w+)', $wrapper, $param_name);
                    }

                    $route->regex = str_replace($search, $replace, $route->regex);

                    // setting route parameters
                    $route->params[$param_name] = '';
                }

                // complete regex pattern
                $route->regex = '/^' . $name . '\/?' . $route->regex . '/';
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