<?php
/**
 * config session
 */

return array(
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////

    'lifetime' => 120, // second
    'files' => APP_ROOT . '/_sessions',
    'path' => '/',
    'secure' => false,
);

?>