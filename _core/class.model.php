<?php
/**
 * model
 */

/**
 * model
 */
class Model
{
    const TYPE_OBJECT   = 'object';
    const TYPE_INT      = 'integer';
    const TYPE_BOOL     = 'boolean';
    const TYPE_DOUBLE   = 'double';
    const TYPE_ARRAY    = 'array';
    const TYPE_RESOURCE = 'resource';
    const TYPE_NULL     = 'NULL';
    const TYPE_STRING   = 'string';

    /**
     * states
     */
    const TRUE  = 'TRUE';
    const FALSE = 'FALSE';

    public $time;
    public $db;
    public $select;

    /**
     * costructor
     */
    public function __construct()
    {
        $this->time   = time();
        $this->db     = new DB;
        $this->select = new Select($this->db);
    }

    /**
     * default value
     *
     * @param mixed $val           - value
     * @param mixed $default_value - default return
     *
     * return mixed
     */
    public function defaultValue($val, $default_value)
    {
        return (false === empty($val) ? $val : $default_value);
    }

}
?>