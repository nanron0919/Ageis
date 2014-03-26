<?php
/**
 * sql statement
 */

/**
 * sql statement
 */
class Sql
{
    protected $temp_sql_array = array();
    protected $db;

    /**
     * constructor
     *
     * @param object $db_instance - db instance
     */
    public function __construct($db_instance = null)
    {
        $this->db = (false === empty($db_instance) ? $db_instance : new DB);
    }

    /**
     * clear temporary data
     *
     * @return null
     */
    public function clear()
    {
         $this->temp_sql_array = array();
    }
}
?>