<?php
/**
 * bootstrap
 */
$included = get_included_files();
define('FRAMEWORK_ROOT', dirname(__FILE__)); // without trailing slash
define('APP_ROOT', dirname(dirname($included[0]))); // without trailing slash

// autoload application
include_once(FRAMEWORK_ROOT . '/_core/class.application.php');

header("Content-Type:text/html; charset=utf-8");
$application = new Application;
$application->run();

?>