<?php
/**
 * class loader
 */
class Loader
{
    /**
     * loadCore - loadCore
     *
     * @return null
     */
    public static function loadCore()
    {
        self::_load(FRAMEWORK_ROOT . '/_core/');

        return new Loader;
    }

    /**
     * loadLibrary - loadLibrary
     *
     * @return null
     */
    public static function loadLibrary()
    {
        self::_load(FRAMEWORK_ROOT . '/_lib/');

        return new Loader;
    }

    /**
     * loadException - loadException
     *
     * @return null
     */
    public static function loadException()
    {
        self::_load(FRAMEWORK_ROOT . '/_exceptions/');

        return new Loader;
    }

    /**
     * loadHelper - loadHelper
     *
     * @param string $helper_name - helper name
     *
     * @return null
     */
    public static function loadHelper($helper_name)
    {
        self::_load(APP_ROOT . '/_helpers/' . $helper_name . '.php');

        return new Loader;
    }

    /**
     * loadController - loadController
     *
     * @param string $controller_name - controller name
     *
     * @return string
     */
    public static function loadController($controller_name)
    {
        $filepath = self::normalizeFilePath($controller_name, 'controller');
        $fullpath = $filepath['fullpath'];
        $class_name = $filepath['class_name'];

        self::_load(Config::controller()->path . $fullpath);

        if (false === class_exists($class_name)) {
            throw new ActionException(Config::exception()->action->ex5002, $class_name);
        }

        return new $class_name;
    }

    /**
     * loadModel - loadModel
     *
     * @param string $model_name - model name
     *
     * @return class
     */
    public static function loadModel($model_name)
    {
        $filepath   = self::normalizeFilePath($model_name, 'model');
        $fullpath   = $filepath['fullpath'];
        $class_name = $filepath['class_name'];

        self::_load(Config::model()->path . $fullpath);

        if (false === class_exists($class_name)) {
            throw new ActionException(Config::exception()->action->ex5002, $class_name);
        }

        return new $class_name;
    }

    /**
     * loadI18n - loadI18n
     *
     * @param string $i18n_name       - i18n name
     * @param string $active_language - active language
     *
     * @return null
     */
    public static function loadI18n($i18n_name, $active_language)
    {
        Config::add('i18n', 'default', $active_language);
        $config = Config::i18n();
        $filename = preg_replace('/(\/)?([\w-]+)$/', '$1lang.$2.php', $i18n_name);

        return self::_load($config->path . '/' . $config->default . '/' . $filename, false);
    }

    ////////////////////
    // helper methods //
    ////////////////////

    /**
     * normalizeFilePath - normalize file path
     *
     * @param string $filename - filename (may include child folder)
     * @param string $prefix   - prefix of filename
     *
     * @return array
     */
    protected static function normalizeFilePath($path, $prefix = '')
    {
        $parts = explode('/', $path);
        $module = end($parts);
        $filename = sprintf('%s.%s.php', $prefix, $module);
        unset($parts[count($parts) - 1]);

        $folder = implode('/', $parts);
        $folder = (false === empty($folder) ? '/' . $folder : '');

        return array(
            'folder'   => $folder,
            'filename' => $filename,
            'fullpath' => $folder . '/' . $filename,
            'class_name' => ucfirst($prefix) . self::normalizeName($module)
        );
    }

    /**
     * normalizeName - normalize name
     *
     * @param string $name - name
     *
     * @return string
     */
    protected static function normalizeName($name)
    {
        $parts = explode('-', $name);

        $parts = array_map(
            function ($part_name) {
                return ucfirst($part_name);
            },
            $parts
        );

        return implode('', $parts);
    }

    /////////////////////
    // private methods //
    /////////////////////

    /**
     * _load - load
     *
     * @param string $dir  - directory (without trailing slash)
     * @param bool   $once - require once
     *
     * @return array - files
     */
    private static function _load($fullpath, $once = true)
    {
        $return = array();

        if (true === is_readable($fullpath)) {
            if (true === is_dir($fullpath)) {
                $files = self::_globRecursive($fullpath);

                foreach ($files as $file) {
                    call_user_func_array(array('self', '_load'), array($file, $once));
                    $return[basename($file)] = require_once($file);
                }
            }
            else {
                if (false === $once) {
                    $return = require($fullpath);
                }
                else {
                    $return = require_once($fullpath);
                }
            }
        }
        else {
            throw new ActionException(Config::exception()->action->ex5002, $fullpath);
        }

        return $return;
    }

    /**
     * _globRecursive - glob recursive
     *
     * @param string $directory - directory (without trailing slash)
     *
     * @return array
     */
    private static function _globRecursive($directory) {
        $files = array();


        foreach(glob($directory . '/*', GLOB_NOSORT) as $item) {

            if (true === is_dir($item)) {
                $files = array_merge(self::_globRecursive($item), $files);
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
}

?>