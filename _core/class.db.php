<?php
/**
 * access database
 */

/**
 * database
 */
class DB
{
    public $provider;
    public $mysqli;

    /**
     * costructor
     *
     * @param object $config - database config
     */
    public function __construct($config)
    {
        $this->mysqli = new mysqli(DATABASE_LOCATION, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DB_NAME);
        $this->mysqli->set_charset(DATABASE_CHARSET);
    }

    /**
     * selectDb
     *
     * @param string $dbname - db name
     *
     * @return null
     */
    public function selectDb($dbname)
    {
        if (!$this->mysqli->select_db($dbname)) {
            // TODO: throw exception
        }
    }

    /**
     * query returns hash array
     *
     * @param string $hashkey - hash key
     * @param string $sql     - sql
     *
     * @return array
     */
    public function queryHash($hashkey, $sql)
    {
        $args = array_slice(func_get_args(), 1);
        $rows = call_user_func_array(array($this, 'query'), $args);
        $temp = array();

        foreach ($rows as $key => $row) {
            if (false === empty($row->$hashkey)) {
                $temp[$row->$hashkey] = $row;
            }
        }

        unset($rows);
        return $temp;
    }

    /**
     * query
     *
     * @param string $sql - sql
     *
     * @return array
     */
    public function query($sql)
    {
        $args   = array_slice(func_get_args(), 1);
        $sql    = $this->realizeSqlString($sql, $args);
        $result = $this->_exec($sql);
        $stdCls = new stdClass;
        $rows   = array();

        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $rows[] = (object)$row;
        }

        $result->free();

        return $rows;
    }

    /**
     * update an entry
     *
     * @param string $table_name - table name
     * @param array  $params     - key value pair args for updated values
     * @param bool   $conditions - for check what entries will update (0: column name, 1: value, 2: compare)
     *
     * @return int - affect rows
     */
    public function update($table_name, $params, $conditions)
    {
        $table_name = $this->wrapByGraveAccent($table_name);
        $sql = 'UPDATE %s SET %s WHERE %s';

        $fields = array();
        $where  = array();
        $values = array();// array_values($params);

        foreach ($params as $key => $val) {
            $field = $this->wrapByGraveAccent($key);

            if (true === is_array($val)) {
                $compute = (false === empty($val[1]) ? $val[1] : '');
                $fields['set'][] = sprintf('%s = %s %s ?', $field, $field, $compute);
                array_push($values, $val[0]);
            }
            else {
                $fields['set'][] = sprintf('%s = ?', $field);
                array_push($values, $val);
            }
        }

        foreach ($conditions as $row) {
            $column  = $this->wrapByGraveAccent($row[0]);
            $value   = $row[1];
            $compare = (false === empty($row[2]) ? $row[2] : '=');

            $fields['where'][] = sprintf('%s %s ?', $column, $compare);

            $values[] = $value;
        }

        $sql = sprintf(
            $sql,
            $table_name,
            implode(', ', $fields['set']),
            implode(' AND ', $fields['where'])
        );

        $sql = $this->realizeSqlString($sql, $values);
        $result = $this->_exec($sql);

        return $this->mysqli->affected_rows;
    }

    /**
     * add a new entry
     *
     * @param string $table_name          - table name
     * @param array  $args                - key value pair args
     * @param bool   $update_on_duplicate - update on duplicate
     * @param array  $update_exclude      - update exclude
     *
     * @return int - insert id
     */
    public function addRow($table_name, $args, $update_on_duplicate = false, $update_exclude = array())
    {
        $sql = "INSERT INTO %s (%s) VALUES (%s)";
        $update_sql = ' ON DUPLICATE KEY UPDATE %s';

        $fields = array();
        $update_fields = array_values(array_diff(array_keys($args), (array)$update_exclude));
        $params = array(
            'insert'    => array_pad(array(), count($args), '?'),
            'on_update' => array_pad(array(), count($update_fields), '%s = ?')
        );
        $values = array();

        foreach ($args as $key => $value) {
            $fields['insert'][] = $this->wrapByGraveAccent($key);
            $values[] = $value;
        }

        $sql = sprintf(
            $sql,
            $this->wrapByGraveAccent($table_name),
            implode(', ', $fields['insert']),
            implode(', ', $params['insert'])
        );

        if (true === $update_on_duplicate) {
            foreach ($update_fields as $key => $val) {
                $params['on_update'][$key] = sprintf($params['on_update'][$key], $this->wrapByGraveAccent($val));
                $values[] = $args[$val];
            }

            $sql .= sprintf($update_sql, implode(', ', $params['on_update']));
        }

        $sql = $this->realizeSqlString($sql, $values);
        $result = $this->_exec($sql);
        $insert_id = $this->mysqli->insert_id;

        return (false === empty($insert_id) ? $insert_id : 0);
    }

    /**
     * realize sql statement
     *
     * @param string $sql    - sql
     * @param array  $values - values
     *
     * @return string - escape string
     */
    public function realizeSqlString($sql, $values)
    {
        $params = array(
            str_replace('?', '%s', $sql),
        );

        foreach ($values as $val) {
            $params[] = '"' . $this->escape($val) . '"';
        }

        return call_user_func_array('sprintf', $params);
    }

    /**
     * escape special charater
     *
     * @param string $unescape_string - unescape string
     *
     * @return string - escape string
     */
    public function escape($unescape_string)
    {
        if (true === is_string($unescape_string)) {
            return $this->mysqli->real_escape_string($unescape_string);
        }
        else {
            return $unescape_string;
        }
    }

    /**
     * wrap by grave accent
     *
     * @param string $field - table field
     *
     * @return string
     */
    public function wrapByGraveAccent($str)
    {
        return preg_replace('/(\w+)/', '`\1`', $str);
    }

    /**
     * execute sql command
     *
     * @param string $sql - sql statement with parameter
     *
     * @return array
     */
    private function _exec($sql)
    {
        $result  = $this->mysqli->query($sql);
        $errno   = mysqli_errno($this->mysqli);
        $err_msg = mysqli_error($this->mysqli);

        if (0 !== $errno) {
            // TODO: throw exception
            Application::debug(sprintf('db query error(%d): %s', $errno, $err_msg));
            exit;
        }

        return $result;
    }
}
?>