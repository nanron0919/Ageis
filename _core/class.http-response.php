<?php
/**
 * class HttpResponse
 */

namespace Ageis;

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
     * @param object|array $obj      - object wants to be convert into json
     * @param bool         $is_jsonp - is jsonp
     *
     * @return null
     */
    public static function json($obj, $is_jsonp = false)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo json_encode($obj);
    }
}
?>