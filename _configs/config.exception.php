<?php
/**
 * config exception
 */
return array(
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////

    'application' => array(
        'ex1001' => array(
            'code'    => 1001,
            'message' => 'File/Directory is not existing.'
        ),
        'ex1002' => array(
            'code'    => 1002,
            'message' => 'Class is not existing.'
        ),
        'ex1003' => array(
            'code'    => 1003,
            'message' => 'Environment config doesnt exist.'
        ),
    ),
    'datatype' => array(
        'ex2001' => array(
            'code'    => 2001,
            'message' => 'Data type is not match.'
        ),
        'ex2002' => array(
            'code'    => 2002,
            'message' => 'Value is out of range.'
        ),
        'ex2003' => array(
            'code'    => 2003,
            'message' => 'Value is not defined.'
        ),
    ),
    'database' => array(
        'ex3001' => array(
            'code'    => 3001,
            'message' => 'Field doesn\' exist.'
        ),
        'ex3002' => array(
            'code'    => 3002,
            'message' => 'Operating error (%s, %s).'
        ),
        'ex3003' => array(
            'code'    => 3003,
            'message' => 'No value can be update or insert.'
        ),
    ),
    'model' => array(
        'ex4001' => array(
            'code'    => 4001,
            'message' => 'Model is missing setting.'
        ),
        'ex4002' => array(
            'code'    => 4002,
            'message' => 'Missing a part of SQL.'
        ),
        'ex4003' => array(
            'code'    => 4003,
            'message' => 'Not a model.'
        ),
    ),
    // action exceptions are for controller or view rendering
    'action' => array(
        'ex5001' => array(
            'code'    => 5001,
            'message' => 'View (%s) is not existing.'
        ),
        'ex5002' => array(
            'code'    => 5002,
            'message' => 'Module (%s) is not existing.'
        ),
    ),
    'upload' => array(
        'ex6001' => array(
            'code'    => 6001,
            'message' => 'Uploaded file is not existing.'
        ),
        'ex6002' => array(
            'code'    => 6002,
            'message' => 'Uploaded file is not acceptable.'
        ),
    ),
);

?>