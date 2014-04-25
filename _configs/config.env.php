<?php
/**
 * env setting
 */

// config set is group by host and the default must be exist
$configs = array(
    'default' => array(
        'env'              => 'development',
        'debug'            => true,
        'is_cli'           => (php_sapi_name() === 'cli')
    )
);

// get config
if (true === array_key_exists($_SERVER['HTTP_HOST'], $configs)) {
    $config = $configs[$_SERVER['HTTP_HOST']];
}
else {
    $config = $configs['default'];
}

return $config;
?>