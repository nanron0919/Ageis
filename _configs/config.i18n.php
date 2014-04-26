<?php
/**
 * i18n setting
 */
return array(
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////

    /**
     * setting language file where is located
     */
    'path'        => APP_ROOT . '/_i18n',

    /**
     * default language folder in path
     */
    'default'     => 'zh-tw',

    /**
     * auto detect the language what user suppose to use, according to following 'detect_order'
     */
    'auto_detect'  => false,
    'detect_order' => array(
        // fetching broswer language
        'browser',
        // check language has exist for domain
        'domain'
    ),
    'locale_map'   => array(
        'tw' => 'zh-tw'
    )
);
?>