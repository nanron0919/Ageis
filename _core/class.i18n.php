<?php
/**
 * to get multiple language setting.
 */

/**
 * i18n
 */
final class i18n
{
    private static $_cache_langs = array();

    /**
     * get line from language file
     *
     * @param string $namespace - filename as namespace separates by slash (last one should be filename)
     * @param string $key       - key separates by slash as well (last one should be a key)
     * @param bool   $escape    - escape string
     *
     * @return null
     */
    public static function line($namespace, $key = '', $escape = true)
    {
        $_lang = '';

        $filename = preg_replace('/(\/)?([\w-]+)$/', '$1lang.$2.php', $namespace);

        $fullpath = APP_ROOT . sprintf(I18N_LANGUAGE_PATH, I18N_DEFAULT) . '/' . $filename;

        if (true === is_readable($fullpath)) {
            // default by a empty array
            $lang = array();
            include($fullpath);
            self::$_cache_langs[$fullpath] = $lang;
            $_lang = $lang;
            // get lang array

            $key_parts = explode('/', $key);

            foreach ($key_parts as $key) {
                $_lang = self::_line($key, $_lang, $escape);
            }
        }

        return $_lang;
    }

    /**
     * fetch line from lang file
     *
     * @param string $key    - key
     * @param array  $lang   - lang array
     * @param bool   $escape - escape (true by default)
     *
     * @return mixed
     */
    private static function _line($key, $lang, $escape = true)
    {
        $escape = (true === is_bool($escape) ? $escape : true);
        $result = '';

        if (true === is_array($lang) && true === array_key_exists($key, $lang)) {
            $result = $lang[$key];
        }
        else {
            $result = $lang;
        }

        if (true === $escape) {
            $result = self::escape($result);
        }

        return $result;
    }

    /**
     * escape whole array
     *
     * @param array $ary - array
     *
     * @return mixed
     */
    public static function escape($lang)
    {
        if (true === is_array($lang)) {
            foreach ($lang as &$val) {
                if (true === is_array($val)) {
                    self::escape($val);
                }
                else {
                    $val = htmlspecialchars($val);
                }
            }
        }
        else {
            $lang = htmlspecialchars($lang);
        }

        return $lang;
    }

    /**
     * set active language - set as const named I18N_ACTIVE
     *
     * @return null
     */
    public static function setActiveLanguage()
    {
        $mapping_func = array(
            'domain'  => 'detectDomainLanguage',
            'browser' => 'detectBroserLanguage'
        );
        $settings = self::parseOrderSetting();
        $support_langs = self::getSupportLanguages();
        $langs = array();

        foreach ($mapping_func as $key => $value) {
            $langs = array_merge($langs, call_user_func('self::' . $mapping_func[$key]));
        }

        $intersect_langs = array_intersect($langs, $support_langs);

        if (false === empty($intersect_langs)) {
            define('I18N_ACTIVE', $intersect_langs[0]);
        }
        else {
            define('I18N_ACTIVE', I18N_DEFAULT);
        }
    }

    /**
     * get language setting from browser
     *
     * @return array - always lowercase
     */
    public static function detectBroserLanguage()
    {
        $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $language = strtolower(false === empty($language) ? $language : '');
        $langs    = array();
        preg_match('|^(?P<language_code>\w{2})?[-]?(?P<location>\w+)?|i', $language, $matches);

        if (false === empty($matches['language_code'])) {
            if (false === empty($matches['language_code'])) {
                $langs[] = $matches['language_code']. '-' . $matches['location'];
            }

            $langs[] = $matches['language_code'];
        }

        return $langs;
    }

    /**
     * get language setting from domain
     *
     * @return array - always lowercase
     */
    public static function detectDomainLanguage()
    {
        $settings       = self::parseOrderSetting();
        $setting        = $settings['domain'];
        $langs          = array();
        $hostname_parts = explode('.', HOSTNAME);

        $map = array($hostname_parts[0], end($hostname_parts));

        foreach ($map as $location) {
            if (true === array_key_exists($location, $setting)) {
                $langs[] = strtolower($setting[$location]);
            }
        }

        return array_unique($langs);
    }

    /**
     * get language setting from domain
     *
     * @return array - always uppercase
     */
    public static function parseOrderSetting()
    {
        $order   = explode(';', I18N_DETECT_ORDER);
        $mapping = array();

        foreach ($order as $o) {
            $match = preg_match_all('/\w+-?\w+/', $o, $matches);
            $matches = end($matches);

            $mapping[$matches[0]] = array();

            for ($i = 1; $i < count($matches); $i+=2) {
                $mapping[$matches[0]][$matches[$i]] = $matches[$i + 1];
            }
        }

        return $mapping;
    }

    /**
     * get support languages depends on the folders that were existing
     *
     * @return array
     */
    public static function getSupportLanguages()
    {
        $langs = array();

        foreach (glob(APP_ROOT . '/_i18n/*', GLOB_ONLYDIR) as $item) {
            $items           = explode('/', $item);
            $dirname         = end($items);
            $langs[] = $dirname;
        }

        return $langs;
    }
}
?>
