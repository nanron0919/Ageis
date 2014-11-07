<?php
/**
 * autoload
 */
$included = get_included_files();
define('FRAMEWORK_ROOT', dirname(__FILE__)); // without trailing slash
define('APP_ROOT', dirname(dirname($included[0]))); // without trailing slash

// load core module
include FRAMEWORK_ROOT . '/_core/class.loader.php';
Ageis\Loader::loadCore()->loadLibrary()->loadException();
unset($included);

foreach (Ageis\Config::helper()->autoload_helpers as $helper) {
    Ageis\Loader::loadHelper($helper);
}

return new Ageis\Application;
?>