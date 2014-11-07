<?php
/**
 * to get multiple language setting.
 */

namespace Ageis;

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
        $active_language = self::getActiveLanguage();
        $lang = Loader::loadI18n($namespace, $active_language);

        if (false === empty($lang)) {
            // default by a empty array
            self::$_cache_langs[$namespace] = $lang;
            $_lang = $lang;
            // get lang array

            $key_parts = explode('/', $key);

            foreach ($key_parts as $key) {
                $_lang = self::_line($key, $_lang, $escape);
            }
        }

        return Converter::arrayToObject($_lang);
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
        $hostname_parts = explode('.', Url::host());

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
        $config  = Config::i18n();
        $order   = $config->detect_order;
        $mapping = array();

        foreach ($order as $o) {
            if ('domain' === $o) {
                $mapping[$o] = (object) $config->locale_map;
            }
            else {
                $mapping[$o] = new \stdClass;
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

        foreach (glob(Config::i18n()->path . '/*', GLOB_ONLYDIR) as $item) {
            $items   = explode('/', $item);
            $dirname = end($items);
            $langs[] = $dirname;
        }

        return $langs;
    }

    /////////////////////
    // private methods //
    /////////////////////

    /**
     * getActiveLanguage - get active language
     *
     * @return string
     */
    public static function getActiveLanguage()
    {
        $config = Config::i18n();

        $mapping_func  = array(
            'domain'  => 'detectDomainLanguage',
            'browser' => 'detectBroserLanguage'
        );
        $settings      = self::parseOrderSetting();
        $support_langs = self::getSupportLanguages();
        $active_language = $config->default;
        $langs = array();

        foreach ($mapping_func as $key => $value) {
            $langs = array_merge($langs, call_user_func('self::' . $mapping_func[$key]));
        }

        $intersect_langs = array_intersect($langs, $support_langs);

        if (false === empty($intersect_langs[0])) {
            $active_language = $intersect_langs[0];
        }

        return $active_language;
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
}
?>
