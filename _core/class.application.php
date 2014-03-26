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
        session_start();

        $this::loadCore();
        $this::loadException();
        $this::loadController();
        $this->route = new Route;
        $this->defineAppVariable($this->loadConfig());
    }

    /**
     * load config
     *
     * @return array - config
     */
    public function loadConfig()
    {
        require_once(APP_ROOT . '/_configs/config.env.php');
        return $config;
    }

    /**
     * load
     *
     * @param string $method_name - method name
     * @param array  $args        - arguments
     *
     * @return array - config
     */
    public static function __callStatic($method_name, $args)
    {
        $name = (false === empty($args[0]) ? $args[0] : '');
        $type = str_replace('load', '', strtolower($method_name));
        $type_mappign_folder = array(
            'controller'       => array(
                'folder'    => '_controllers',
                'prefix'    => 'controller',
                'base_path' => APP_ROOT
            ),
            'core'       => array(
                'folder'    => '_core',
                'prefix'    => '',
                'base_path' => FRAMEWORK_ROOT
            ),
            'exception'  => array(
                'folder'    => '_exceptions',
                'prefix'    => 'exception',
                'base_path' => FRAMEWORK_ROOT
            ),
            'helper'     => array(
                'folder'    => '_helpers',
                'prefix'    => '',
                'base_path' => APP_ROOT
            ),
            'lib'        => array(
                'folder'    => '_lib',
                'prefix'    => '',
                'base_path' => FRAMEWORK_ROOT
            ),
            'model'      => array(
                'folder'    => '_models',
                'prefix'    => 'model',
                'base_path' => APP_ROOT
            ),
        );

        // check type is acceptable
        if (true === array_key_exists($type, $type_mappign_folder)) {
            $type_config = $type_mappign_folder[$type];
            $prefix   = (false === empty($type_config['prefix']) ?  $type_config['prefix'] : 'class');
            $filename = (false === empty($name) ? $name . '.php' : '');
            $filename = (false === empty($prefix) && false === empty($name)
                        ? $prefix . '.' . $filename
                        : $filename);
            $path     = sprintf('%s/%s', $type_config['base_path'], $type_config['folder']);
            $fullpath = $path . '/' .  $filename;
        }
        else {
            // TODO: throw exception
        }

        if (true === is_readable($fullpath)) {
            if (true === is_dir($fullpath)) {
                $files = self::globRecursive($path);

                foreach ($files as $file) {
                    if (0 < preg_match('/' . $prefix . '[.].*\w+[.]php$/', $file)) {
                        require_once($file);
                    }
                }
            }
            else {
                require_once($fullpath);
            }
        }
        else {
            // TODO: throw exception
        }
    }

    /**
     * define system variable
     *
     * @param array  $config - application config
     * @param string $prefix - config prefix
     *
     * @return null
     */
    public function defineAppVariable($config, $prefix = '')
    {
        $prefix = (false === empty($prefix) ? $prefix . '_' : '');

        foreach ($config as $key => $val) {
            if (true === is_array($val)) {
                $this->defineAppVariable($val, $prefix . $key);
            }
            else {
                define(strtoupper($prefix . $key), $val);
            }
        }
    }

    /**
     * glob recursive
     *
     * @param string $directory - directory (without trailing slash)
     *
     * @return null
     */
    public static function globRecursive($directory) {
        $files = array();


        foreach(glob($directory . '/*', GLOB_NOSORT) as $item) {

            if (true === is_dir($item)) {
                $files = array_merge(self::globRecursive($item), $files);
            }
            else {
                $files[] = $item;
            }
        }

        // sort by filename and directory always in the end
        usort(
            $files,
            function($a, $b) {
                if (dirname($a) === dirname($b)) {
                    return $a > $b;
                }
                else {
                    return dirname($a) > dirname($b);
                }
            }
        );

        return $files;
    }

    /**
     * run application
     *
     * @return null
     */
    public function run()
    {
        $route           = $this->route->findMatchRoute();
        $controller_name = 'Controller_' . ucfirst($route['controller']);

        if (false === empty($route) && true === class_exists($controller_name)) {
            $controller = new $controller_name($route);
            $controller->run();
        }
        else {
            echo 'redirect';
            // TODO: not really does redirect yet.
            // Http::redirect('http://google.com');
        }
    }

    /**
     * debug
     *
     * @param mixed $var - variable
     *
     * @return null
     */
    public static function debug($var)
    {
        if (false === defined('ENV') || 'development' === ENV) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }

    /**
     * load route
     *
     * @return null
     */
    public function loadRoute()
    {
        $this->route->findMatchRoute();
    }

}
?>
