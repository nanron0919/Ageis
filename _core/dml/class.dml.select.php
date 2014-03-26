<?php
/**
 * dml select
 */

/**
 * dml select
 */
class Select extends Sql
{
    // parts
    const FROM      = 'from';
    const JOIN      = 'join';
    const ORDER_BY  = 'orderBy';
    const GROUP_BY  = 'groupBy';
    const WHERE     = 'where';
    const FIELD     = 'field';
    const LIMIT     = 'limit';
    const VALUE     = 'value';

    // sort
    const SORT_ASC  = 'ASC';
    const SORT_DESC = 'DESC';

    // join
    const JOIN_INNER  = 'INNER JOIN';
    const LEFT_INNER  = 'LEFT JOIN';
    const RIGHT_INNER = 'RIGHT JOIN';

    // sequence
    private $_sequence = array(
        self::FIELD => array('separator' => ', ', 'head' => 'SELECT', 'default' => '*'),
        self::FROM => array('separator' => ', ', 'head' => 'FROM'),
        self::JOIN => array('separator' => ', ', 'head' => ''),
        self::WHERE => array('separator' => ' AND ', 'head' => 'WHERE'),
        self::GROUP_BY => array('separator' => ', ', 'head' => 'GROUP BY'),
        self::ORDER_BY => array('separator' => ', ', 'head' => 'ORDER BY'),
        self::LIMIT => array('separator' => ', ', 'head' => 'LIMIT'),
    );

    /**
     * constructor
     *
     * @param object $db_instance - db instance
     */
    public function __construct($db_instance = null)
    {
        parent::__construct($db_instance);
    }

    /**
     * limit result
     *
     * @return object - Select
     */
    public function limit()
    {
        $args = func_get_args();
        $start = (false === empty($args[0]) ? (int)$args[0] : null);
        $limit = (false === empty($args[1]) ? (int)$args[1] : null);

        // have both $start and $limit
        if (false === empty($start) && false === empty($limit)) {
            $this->temp_sql_array[self::LIMIT] = array($start, $limit);
        }
        // have $start but $limit
        else if (false === empty($start) && true === empty($limit)) {
            $this->temp_sql_array[self::LIMIT] = array($start, 1);
        }
        // have no $start but $limit
        else if (true === empty($start) && false === empty($limit)) {
            $this->temp_sql_array[self::LIMIT] = array(0, $limit);
        }

        return $this;
    }

    /**
     * what table wanna fetch
     *
     * @param string $table_name - table name
     *
     * @return object - Select
     */
    public function from($table_name)
    {
        $this->temp_sql_array[self::FROM] = $this->db->wrapByGraveAccent($table_name);
        return $this;
    }

    /**
     * what table wanna join
     *
     * @param string $table_name     - table name
     * @param string $join_column    - column name
     * @param string $match_table    - match table name
     * @param string $match_column   - match column name
     * @param string $join_direction - join direction
     *
     * @return object - Select
     */
    public function join($join_table, $join_column, $match_table, $match_column, $join_direction = self::JOIN_INNER)
    {
        $this->temp_sql_array[self::JOIN][]
            = $join_direction . ' ' . $this->db->wrapByGraveAccent(
                sprintf('%s.%s = %s.%s', $join_table, $join_column, $match_table, $match_column)
            );
        return $this;
    }

    /**
     * order by which column
     *
     * @param string $table_name - table name
     * @param string $column     - column name
     * @param string $sortby     - asc / desc
     *
     * @return object - Select
     */
    public function orderBy($table_name, $column, $sortby = self::SORT_ASC)
    {
        $this->temp_sql_array[self::ORDER_BY][]
            = $this->db->wrapByGraveAccent(sprintf('%s.%s', $table_name, $column)) . ' ' . $sortby;
        return $this;
    }

    /**
     * group by which column
     *
     * @param string $table_name - table name
     * @param string $column     - column name
     *
     * @return object - Select
     */
    public function groupBy($table_name, $column)
    {
        $this->temp_sql_array[self::GROUP_BY][]
            = $this->db->wrapByGraveAccent(sprintf('%s.%s', $table_name, $column));
        return $this;
    }

    /**
     * set condition
     *
     * @param string $table_name - table name
     * @param string $column     - column name
     * @param mixed  $value      - value
     * @param string $comparer   - comparer
     *
     * @return object - Select
     */
    public function where($table_name, $column, $value, $comparer = '=')
    {
        $comparer = (false === empty($comparer) ? $comparer : '=');

        if (false === is_array($value)) {
            $this->temp_sql_array[self::WHERE][]
                = $this->db->wrapByGraveAccent(sprintf('%s.%s %s ?', $table_name, $column, $comparer));
            $this->temp_sql_array[self::VALUE][] = $value;
        }
        else {
            $questions = array_pad(array(), count($value), '?');

            $this->temp_sql_array[self::WHERE][]
                = $this->db->wrapByGraveAccent(sprintf('%s.%s', $table_name, $column))
                . sprintf(' IN (%s)', implode(', ', $questions));
            $this->temp_sql_array[self::VALUE] = array_merge($this->temp_sql_array[self::VALUE], $value);
        }

        return $this;
    }

    /**
     * set field
     *
     * @param string $column     - column name
     * @param string $table_name - table name
     *
     * @return object - Select
     */
    public function fields($column, $table_name = '')
    {
        $table_name = (false === empty($table_name) ? $table_name . '.' : '');
        $this->temp_sql_array[self::FIELD][]
            = $this->db->wrapByGraveAccent(sprintf('%s%s', $table_name, $column));
        return $this;
    }

    /**
     * query do query!!
     *
     * @param string $hash_key - return hash array
     *
     * @return array
     */
    public function query($hash_key = '')
    {
        $return_hash = (false === empty($hash_key));
        $sql = '';

        foreach ($this->_sequence as $key => $row) {
            if (false === empty($this->temp_sql_array[$key])) {
                $sql .= sprintf(
                    '%s %s ',
                    $row['head'],
                    implode($row['separator'], (array)$this->temp_sql_array[$key])
                );
            }
            else if (false === empty($row['default'])) {
                $sql .= sprintf('%s %s ', $row['head'], $row['default']);
            }
        }

        if (true === $return_hash) {
            $params = array($hash_key, $sql);
        }
        else {
            $params = array($sql);
        }

        if (false === empty($this->temp_sql_array[self::VALUE])) {
            $params = array_merge($params, $this->temp_sql_array[self::VALUE]);
        }

        $method_name = (true === $return_hash ? 'queryHash' : 'query');
        $rows = call_user_func_array(array($this->db, $method_name), $params);
        $this->clear();

        return $rows;
    }
}
?>
