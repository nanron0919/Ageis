<?php
/**
 * database setting
 */
return array(
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////

    // setting by env
    'development' => array(
        'default' => array(
            'host'     => 'localhost',
            'driver'   => 'mysql',  // using /_core/drivers/class.mysql.php to create a database connection
            'database' => 'database',
            'user'     => 'database',
            'password' => 'password',
            'timezone' => '+8:00',
            'charset'  => 'utf8'
        )
    ),
    'production' => array()
);

?>