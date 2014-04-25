<?php
/**
 * route setting
 */
return array(
    'home'      => array(
        'controller' => 'home',
        'pattern'    => ''
    ),
    'myaccount' => array(
        'controller' => 'myaccount',
        'pattern'    => '(:method(/:action(/:id)))'
    ),
    'product'   => array(
        'controller' => 'product',
        'pattern'    => ':product_id(/:method)'
    ),
    'member'   => array(
        'controller' => 'member',
        'pattern'    => ':unique_token(/:method)'
    ),
    'login'     => array(
        'controller' => 'login',
        'pattern'    => '(:social_network(/:method))'
    ),
    'logout'     => array(
        'controller' => 'login',
        'pattern'    => '(:social_network(/:method))'
    ),
    'upload'     => array(
        'controller' => 'upload',
        'pattern'    => '(:type)'
    )
);
?>