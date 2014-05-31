<?php
/**
 * that does handle all of cache store, clear and generate
 */

/**
 * class caching
 */
class caching
{
    protected $path;
    protected $expired;
    protected $config;

    /**
     * costructor
     *
     * @param string $path    - stored path
     * @param int    $expired - what time does expired
     */
    public function __construct($path = '', $expired = null)
    {
        $this->config = Config::caching();
        $this->path = (false === empty($path) ? $path : $this->config->path);
        $this->expired = (true === isset($expired) ? $expired : $this->config->expire);
    }

    /**
     * store - store cache
     *
     * @param string $key     - key
     * @param string $content - what content that stored
     *
     * @return string
     */
    public function store($key, $content)
    {
        $filename = $this->generatePath($key);

        return File::write($filename, $content);
    }

    /**
     * clear - clear cache
     *
     * @param string $key - what key of content that fetch
     *
     * @return bool
     */
    public function clear($key)
    {
        $filename = $this->generatePath($key);

        return File::delete($filename);
    }

    /**
     * read - read cache
     *
     * @param string $key - key
     *
     * @return string
     */
    public function read($key)
    {
        $filename = APP_ROOT . '/' . $this->generatePath($key);

        return File::read($filename);
    }

    /**
     * generatePath - generate stored path
     *
     * @param string $key - key
     *
     * @return string
     */
    public function generatePath($key)
    {
        $filename = md5($key);
        $last_2_word = substr($filename, -2);

        return sprintf('%s/%s/%s', $this->path, $last_2_word, $filename);
    }

    /**
     * valid - check caching is valid
     *
     * @param string $key - key
     *
     * @return bool
     */
    public function isValid($key)
    {
        $filename = APP_ROOT . '/' . $this->generatePath($key);
        $existing = file_exists($filename);
        $mtime = (true === $existing ? filemtime($filename) : 0);
        $expired = (false === empty($this->expired) ? $this->expired : PHP_INT_MAX);

        return ($mtime + $expired > time());
    }

}
?>