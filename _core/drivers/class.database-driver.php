<?php
/**
 * class database driver
 */

/**
 * class database driver
 */
abstract class DatabaseDriver
{
    protected $connection;
    protected $config;

    /**
     * costructor
     *
     * @param object $config - database config
     */
    public function __construct($config = null)
    {
        if (false === empty($config)) {
            $this->config = $config;

            // build connection
            $this->connection();
        }
        else {
            // TODO: throw an excepion
        }
    }

    /**
     * connection - connection
     *
     * @return null
     */
    abstract public function connection();

    /**
     * disconnect - disconnect
     *
     * @return null
     */
    abstract public function disconnect();

    /**
     * selectDb - select db
     *
     * @param string $db_name - db name
     *
     * @return null
     */
    abstract public function selectDb($db_name);

    /////////////////
    // transaction //
    /////////////////

    /**
     * beginTrans - begin trans
     *
     * @return null
     */
    abstract public function beginTrans();

    /**
     * commit - commit
     *
     * @return null
     */
    abstract public function commit();

    /**
     * rollback - rollback
     *
     * @return null
     */
    abstract public function rollback();

    /**
     * transaction - transaction
     *
     * @param function $func - the function you wanna fire and wrap by a transaction
     *
     * @return null
     */
    public function transaction($func)
    {
        $this->beginTrans();

        try {
            $func();
            $this->commit();
        }
        catch (Exception $e) {
            $this->rollback();
            // TODO: do something
        }
    }

    //////////////////
    // manipulation //
    //////////////////

    /**
     * query - query
     *
     * @param string $sql  - sql
     * @param array  $args - args
     *
     * @return array
     */
    abstract public function query($sql, $args);

    /**
     * escape - escape
     *
     * @param string $unescape_string  - unescape string
     *
     * @return string
     */
    abstract public function escape($unescape_string);

    /////////////
    // helpers //
    /////////////

    /**
     * now - get now through database
     *
     * @return date - now (Y-m-d H:i:s)
     */
    public function now()
    {
        $rows = $this->query('SELECT NOW() AS now');
        return end($rows)->now;
    }

    /**
     * timestamp - get timestamp through now()
     *
     * @return int - timestamp
     */
    public function timestamp()
    {
        return strtotime($this->now());
    }

    /**
     * getType - get data type for bind param
     *
     * @param mixed $val - value
     *
     * @return string
     */
    public function getType($val)
    {
        $config = Config::model();
        $data_type = gettype($val);
        $param_type = (false === empty($config->types->$data_type)
            ? $config->types->$data_type
            : $config->types->string);

        return $param_type;
    }
}
?>