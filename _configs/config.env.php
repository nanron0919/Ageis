<?php
/**
 * env setting
 */

// config set is group by host and the default must be exist
return array(
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////
    'default' => array(
        'env'    => 'development',
        'debug'  => true,
        'error'  => array(
            'e404' => 'static/not-found',
            'e50x' => 'static/error',
        ),
    )
);
?>