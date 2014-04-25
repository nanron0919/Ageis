<?php
/**
 * autoload
 */
$included = get_included_files();
define('FRAMEWORK_ROOT', dirname(__FILE__)); // without trailing slash
define('APP_ROOT', dirname(dirname($included[0]))); // without trailing slash

// load core module
include FRAMEWORK_ROOT . '/_core/class.loader.php';
Loader::loadCore()->loadLibrary()->loadException();

foreach (Config::helper()->autoload_helpers as $helper) {
    Loader::loadHelper($helper);
}

return new Application;
?>