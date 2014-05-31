<?php
/**
 * global redirect function, written by the guys @ http://edoceo.com/creo/php-redirect
 */
class Http
{
    /**
     * Redirects to a given url
     *
     * @param string  $uri  - the url to redirect to
     * @param integer $code - header code to send
     *
     * @return null
     */
    public static function redirect($uri, $code = 302)
    {
        $config = Config::env();

        // if now in cli mode
        if (false === $config->is_cli) {
            // Specific URL
            $location = null;

            if (substr($uri, 0, 4) == 'http') {
                $location = $uri;
            }
            else {
                // prepend the base url incase we forget to pass it
                $location = Url::bindAbsolute($uri);
            }

            $hs = headers_sent();

            if ($hs === false) {
                switch ($code) {
                case 301:
                    // Convert to GET
                    header("301 Moved Permanently HTTP/1.1", true, $code);
                    break;
                case 302:
                    // Conform re-POST
                    header("302 Found HTTP/1.1", true, $code);
                    break;
                case 303:
                    // dont cache, always use GET
                    header("303 See Other HTTP/1.1", true, $code);
                    break;
                case 304:
                    // use cache
                    header("304 Not Modified HTTP/1.1", true, $code);
                    break;
                case 305:
                    header("305 Use Proxy HTTP/1.1", true, $code);
                    break;
                case 306:
                    header("306 Not Used HTTP/1.1", true, $code);
                    break;
                case 307:
                    header("307 Temporary Redirect HTTP/1.1", true, $code);
                    break;
                }
                // TODO: lets do some logging on our redirects here
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
                header("Location: $location");
            }
            // Show the HTML?
            if (($hs==true) || ($code==302) || ($code==303)) {
                // todo: draw some javascript to redirect
                $cover_div_style = 'background-color: #ccc; height: 100%; left: 0px; position: absolute; top: 0px; width: 100%;';
                echo "<div style='$cover_div_style'>\n";
                $link_div_style = 'background-color: #fff; border: 2px solid #f00; left: 0px; margin: 5px; padding: 3px; ';
                $link_div_style.= 'position: absolute; text-align: center; top: 0px; width: 95%; z-index: 99;';
                echo "<div style='$link_div_style'>\n";
                echo "<p>Please See: <a href='$uri'>".htmlspecialchars($location)."</a></p>\n";
                echo "</div>\n</div>\n";
            }

            exit(0);
        }
        else {
            return;
        }
    }

    /**
     * check that is formal url
     *
     * @param string $uri
     *
     * @return bool
     */
    public static function isUrl($url)
    {
        return 0 < preg_match('/(http[s]?:\/\/(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\'|"|:|\<|$|\.\s)/ie', $url);
    }
}
?>
