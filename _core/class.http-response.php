<?php
/**
 * class HttpResponse
 */

/**
 * class HttpResponse
 */
class HttpResponse
{
    /**
     * html - echo html
     *
     * @param string $html - html
     *
     * @return null
     */
    public static function html($html)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /**
     * json - echo json
     *
     * @param string $html - html
     *
     * @return null
     */
    public static function json($obj)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo json_encode($obj);
    }
}
?>