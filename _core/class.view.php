<?php
/**
 * View
 */

/**
 * View
 */
final class View
{
    /**
     * display - display content of view
     *
     * @param string $view_name assign view file (do not need .php)
     * @param array  $data      data binded
     * @param bool   $return    needs return result
     *
     * @return string
     */
    public static function display($view_name, $data = array(), $return = false)
    {
        // getting config
        $config = Config::view();

        // output array each item into a single variable.
        foreach ($data as $key => $item) {
            $$key = $item;
        }

        $filename = preg_replace('/(\/)?([\w-]+)$/', '$1view.$2.php', $view_name);
        $fullpath = sprintf('%s/%s', $config->path, $filename);

        if (true === is_readable($fullpath)) {
            ob_start();
            include $fullpath;
            $content = ob_get_contents();
            ob_end_clean();

            if (true === is_bool($return) && false === $return) {
                HttpResponse::html($content);
            }

            $caching = self::createCaching();
            // uri as a key
            $key = Url::requestUri();

            // cache content
            if (false === $return   // not return
                && true === isset($config->cache)   // has been cache
                && true === $config->cache  // and need cache
                && false === $caching->isValid($key)    // and it doesn't valid
            ) {
                $caching->store($key, $content, 'w');
            }

            return $content;

        }
        else {
            $ex = Config::exception()->action->ex5001;
            throw new ActionException($ex, $view_name);
        }
    }

    /**
     * display - display content of view
     *
     * @param string $view_name - assign view file (do not need .php)
     * @param array  $data      - data binded
     *
     * @return string
     */
    public static function partial($view_name, $data)
    {
        return self::display($view_name, $data, true);
    }

    /**
     * getCache - get cache
     *
     * @return string
     */
    public static function getCache()
    {
        // getting config
        $config = Config::view();
        $caching = self::createCaching();
        $content = '';
        $key = Url::requestUri();

        if (true === $config->cache && true === $caching->isValid($key)) {
            $content = $caching->read($key);
        }

        return $content;
    }

    /**
     * createCache - create cache
     *
     * @param string $key - key
     *
     * @return object
     */
    protected static function createCaching()
    {
        // getting config
        $config = Config::view();

        $caching_path = (false === empty($config->caching_path) ? $config->caching_path : '');
        $expired = (false === empty($config->expired) ? $config->expired : null);

        return new Caching($caching_path, $expired);
    }
}
?>
