<?php
/**
 * class mysql
 */

/**
 * class mysql
 */
final class Mysql extends DatabaseDriver
{
    /**
     * connection - connection
     *
     * @return object - connection
     */
    public function connection()
    {
        if (false === isset($this->connection)) {
            $this->connection = new mysqli($this->config->host, $this->config->user, $this->config->password, $this->config->database);

            if (0 < $this->connection->connect_errno) {
                // TODO: throw an exception
            }

            $this->connection->set_charset($this->config->charset);
            $this->connection->query(sprintf('set timezone SET timezone = "%s"', $this->config->timezone));
        }

        return $this->connection;
    }

    /**
     * disconnect - disconnect
     *
     * @return null
     */
    public function disconnect()
    {
        $this->connection->close();
    }

    /**
     * selectDb - select db
     *
     * @param string $db_name - db name
     *
     * @return null
     */
    public function selectDb($db_name)
    {
        $this->connection->select_db($db_name);
    }

    /////////////////
    // transaction //
    /////////////////

    /**
     * beginTrans - begin trans
     *
     * @return null
     */
    public function beginTrans()
    {
        $this->connection->beginTransaction();
    }

    /**
     * commit - commit
     *
     * @return null
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * rollback - rollback
     *
     * @return null
     */
    public function rollback()
    {
        $this->connection->rollBack();
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
     * @return mixed - false: something wrong
     */
    public function query($sql, $args = array())
    {
        $args = (true === is_array($args) ? $args : array());
        $rows = array();
        $return = false;

        if ($stmt = $this->connection->prepare($sql)) {
            $types = '';
            $params = array(
                &$types
            );

            foreach ($args as $val) {
                $types .= $this->getType($val);
                // set variable as call by reference
                $$val = $val;
                $params[] = &$$val;
            }

            if (false === empty($args)) {
                call_user_func_array(array($stmt, 'bind_param'), $params);
            }

            $stmt->execute();

            $result = $stmt->get_result();

            if (true === is_object($result)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $rows[] = $row;
                }

                $return = Converter::arrayToObject($rows);
            }
            else {
                $return = $this->connection->affected_rows;
            }
        }

        return $return;

    }

    /**
     * escape - escape
     *
     * @param string $unescape_string - unescape string
     *
     * @return string
     */
    public function escape($unescape_string)
    {
        return $this->connection->escape_string($unescape_string);
    }
}
?>