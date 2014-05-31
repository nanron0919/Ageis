<?php
/**
 * config upload
 */
return array(
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////
    'root' => '_tmp',
    'file_filter' => array(
        'jpeg|jpg|png' => 'image',
        'doc|csv|txt' => 'document',
    ),
    'filters' => array(
        'image' => array(
            'folder'  => 'image',
            'max_size' => 10000,
            'options' => array(
                'accept_file_types' => '/^\w+\.(jpeg|jpg|png)$/i'
            ),
        ),
        'document' => array(
            'folder'  => 'doc',
            'max_size' => 10000,
            'options' => array(
                'accept_file_types' => '/^\w+[.](doc|csv|txt)$/i'
            ),
        ),
    ),
);
?>