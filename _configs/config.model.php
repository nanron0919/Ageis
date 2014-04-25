<?php
/**
 * config model
 */
return array(
    'status' => array(
        'true'  => 'TRUE',
        'false' => 'FALSE',
    ),
    /////////////////////////////////////////////
    // essential settings below, do not remove //
    /////////////////////////////////////////////
    'path' => APP_ROOT . '/_models',
    // types
    'types' => array(
        'integer' => 'i',
        'double'  => 'd',
        'float'   => 'd',
        'string'  => 's',
    ),
    // sections
    'sections' => array(
        'from'    => 'from',
        'join'    => 'join',
        'orderBy' => 'orderBy',
        'groupBy' => 'groupBy',
        'where'   => 'where',
        'field'   => 'field',
        'limit'   => 'limit',
        'value'   => 'value',
    ),
    // sort
    'sort' => array(
        'asc'  => 'ASC',
        'desc' => 'DESC',
    ),
    // join
    'join' => array(
        'inner' => 'INNER JOIN',
        'left'  => 'LEFT JOIN',
        'right' => 'RIGHT JOIN',
    ),
    // sequence
    'sequence' => array(
        'select' => array(
            'field'   => array('separator' => ', ', 'head' => 'SELECT', 'required' => true),
            'from'    => array('head' => 'FROM', 'required' => true),
            'join'    => array(),
            'where'   => array('separator' => ' AND ', 'head' => 'WHERE'),
            'groupBy' => array('separator' => ', ', 'head' => 'GROUP BY'),
            'orderBy' => array('separator' => ', ', 'head' => 'ORDER BY'),
            'limit'   => array('separator' => ', ', 'head' => 'LIMIT'),
        ),
        'update' => array(
            'from'   => array('separator' => '', 'head' => 'UPDATE', 'required' => true),
            'field'   => array('separator' => ', ', 'head' => 'SET', 'required' => true),
            'where'   => array('separator' => ' AND ', 'head' => 'WHERE'),
            'limit'   => array('separator' => ', ', 'head' => 'LIMIT'),
        ),
        'insert' => array(
            'from'   => array('separator' => '', 'head' => 'INSERT INTO', 'required' => true),
            'field'   => array('separator' => ', ', 'required' => true),
            'value'   => array('separator' => ', ', 'head' => 'VALUES', 'required' => true),
        ),
        'delete' => array(
            'from'   => array('separator' => '', 'head' => 'DELETE', 'required' => true),
            'where'   => array('separator' => ' AND ', 'head' => 'WHERE', 'required' => true),
            'limit'   => array('separator' => ', ', 'head' => 'LIMIT'),
        ),
    ),
);

?>