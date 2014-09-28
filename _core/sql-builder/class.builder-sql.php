<?php
/**
 * class builder sql
 */

/**
 * class builder sql
 */
abstract class Builder_SQL
{
    protected $temp_sql_array           = array();
    protected $temp_sql_statement_array = array();
    protected $dml_type                 = '';   // select, insert, update or delete
    protected $config                   = null;
    protected $sections                 = null;
    protected $sequence                 = null;
    protected $fill_fields              = array();  // default return anyway

    /**
     * costructor
     */
    public function __construct()
    {
        $this->config    = Config::model();
        $this->exception = Config::exception();
        $this->sections  = $this->getConfig()->sections;
        $this->sequence  = $this->getSequence();

        // default all section
        foreach ($this->sequence as $key => $value) {
            $this->temp_sql_array[$key] = array();
        }

    }

    ////////////////////
    // section setter //
    ////////////////////

    /**
     * limit - limit result
     *
     * @param int $start - start
     * @param int $limit - limit
     *
     * @return object - this
     */
    public function limit($start, $limit)
    {
        $this->temp_sql_array[$this->sections->limit] = array($start, $limit);

        return $this;
    }

    /**
     * from - what table that does operate
     *
     * @param string $table_name - table name
     *
     * @return object - this
     */
    protected function from($table_name)
    {
        $this->temp_sql_array[$this->sections->from] = $this->wrapByGraveAccent($table_name);

        return $this;
    }

    /**
     * leftJoin - left join
     *
     * @param string $join_table - table name
     * @param string $join_field - field name
     * @param string $ref_field  - reference field name
     *
     * @return object - this
     */
    public function leftJoin($join_table, $join_field, $ref_field)
    {
        return $this->join(
            $join_table,
            array('from' => $join_field, 'to' => $ref_field),
            $this->config->join->left
        );
    }

    /**
     * rightJoin - right join
     *
     * @param string $join_table - table name
     * @param string $join_field - field name
     * @param string $ref_field  - reference field name
     *
     * @return object - this
     */
    public function rightJoin($join_table, $join_field, $ref_field)
    {
        return $this->join(
            $join_table,
            array('from' => $join_field, 'to' => $ref_field),
            $this->config->join->right
        );
    }

    /**
     * join - join
     *
     * @param string $join_model  - what model that joined
     * @param array  $join_fields - join fields [from => ..., to => ...]
     * @param string $direction   - inner, left or right (default by INNER JOIN)
     *
     * @return object - this
     */
    public function join($join_table, $join_fields = array(), $direction = '')
    {
        $join_table = $this->wrapByGraveAccent($join_table);
        $join_field = $this->wrapByGraveAccent($join_fields['from']);
        $ref_field  = $this->wrapByGraveAccent($join_fields['to']);
        $direction  = (false === empty($this->config->join->$direction)
            ? $this->config->join->$direction
            : $this->config->join->inner);
        $this->temp_sql_array[$this->sections->join][] = array(
            strtoupper($direction),
            $join_table,
            $join_field,
            $ref_field
        );

        return $this;
    }

    /**
     * orderBy - order by which field
     *
     * @param string $field  - field name
     * @param string $sortBy - asc / desc
     *
     * @return object - this
     */
    public function orderBy($field, $sortBy = '')
    {
        $field = $this->wrapByGraveAccent($field);
        $sortBy = (false === empty($this->config->sort->$sortBy)
            ? $this->config->sort->$sortBy
            : '');
        $this->temp_sql_array[$this->sections->orderBy][] = array($field, $sortBy);

        return $this;
    }

    /**
     * groupBy - group by which column
     *
     * @param string $field - field name
     *
     * @return object - this
     */
    public function groupBy($field)
    {
        $field = $this->wrapByGraveAccent($field);
        $this->temp_sql_array[$this->sections->groupBy][] = $field;

        return $this;
    }

    /**
     * field - set field
     *
     * @param string $field  - field name
     * @param bool   $escape - escape
     *
     * @return object - this
     */
    public function field($field, $escape = false)
    {
        if (false === $escape) {
            $field = $this->wrapByGraveAccent($field);
        }

        $this->temp_sql_array[$this->sections->field][] = $field;

        return $this;
    }

    /**
     * where - set conditions
     *
     * @param string $field      - field name
     * @param mixed  $value      - value
     * @param string $comparator - comparator
     *
     * @return object - this
     */
    public function where($field, $value, $comparator = '=')
    {
        $field      = $this->wrapByGraveAccent($field);
        $comparator = (false === empty($comparator) ? $comparator : '=');
        $comparator = (false === is_array($value) ? $comparator : 'IN');

        // fixed the order of parameters.
        $name = $this->setValue($value);
        $this->temp_sql_array[$this->sections->where][$name] = array($field, $comparator);

        return $this;
    }

    ///////////////
    // build sql //
    ///////////////

    /**
     * build - build sql for each section
     *
     * @return array (0: sql, 1: values)
     */
    public function build()
    {
        // build all section
        foreach ($this->sequence as $key => $item) {
            call_user_func_array(array($this, 'build' . ucfirst($key)) , array());
        }

        $sql = implode(' ', $this->temp_sql_statement_array);
        $result = array(
            preg_replace('/[?]\w+/', '?', $sql)
        );

        $result = array_merge($result, array($this->getValue($sql)));

        // clear all temporary variable
        $this->clear();

        return $result;
    }

    /**
     * getValue - get paremeter from sql statement
     *
     * @return array - values
     */
    protected function getValue($sql)
    {
        // fetch all value
        $values = array();

        if (false === empty($this->temp_sql_array[$this->sections->value])) {
            $values = $this->temp_sql_array[$this->sections->value];
        }

        $result = array();

        // grep parameter name
        preg_match_all('/[?]\w+/', $sql, $matches);

        // make the values are following the order appeared
        foreach ($matches[0] as $name) {
            $value = &$values[$name];

            if (true === is_array($value)) {
                $result = array_merge($result, (array) array_pop($value));
            }
            else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * build - build "from" section
     *
     * @return null
     */
    public function buildFrom()
    {
        $setting = $this->sequence->from;

        if (false === empty($this->temp_sql_array[$this->sections->from])) {
            $table = $this->temp_sql_array[$this->sections->from];
            $this->temp_sql_statement_array[$this->sections->from] =
                sprintf('%s %s', $setting->head, $table);
        }
        else {
            $this->noneSection($setting);
        }
    }

    /**
     * build - build "join" section
     *
     * @return null
     */
    public function buildJoin()
    {
        if (false === empty($this->temp_sql_array[$this->sections->join])) {
            $sql_setting = $this->temp_sql_array[$this->sections->join];
            $this->temp_sql_statement_array[$this->sections->join] = '';

            foreach ($sql_setting as $row) {
                $this->temp_sql_statement_array[$this->sections->join] .=
                    sprintf('%s %s ON %s = %s ', $row[0], $row[1], $row[2], $row[3]);
            }
        }
        else {
            $this->noneSection($this->sequence->join);
        }
    }

    /**
     * build - build "order by" section
     *
     * @return null
     */
    public function buildOrderBy()
    {
        $setting = $this->sequence->orderBy;

        if (false === empty($this->temp_sql_array[$this->sections->orderBy])) {
            $sql_setting = $this->temp_sql_array[$this->sections->orderBy];
            $temp = array();

            // $setting->head;

            foreach ($sql_setting as $val) {
                $temp[] = implode(' ', $val);
            }

            $this->temp_sql_statement_array[$this->sections->orderBy] =
                sprintf('%s %s', $setting->head, implode($setting->separator, $temp));
        }
        else {
            $this->noneSection($setting);
        }
    }

    /**
     * build - build "group by" section
     *
     * @return null
     */
    public function buildGroupBy()
    {
        $setting = $this->sequence->groupBy;

        if (false === empty($this->temp_sql_array[$this->sections->groupBy])) {
            $sql_setting = $this->temp_sql_array[$this->sections->groupBy];
            $this->temp_sql_statement_array[$this->sections->groupBy] =
                sprintf('%s %s', $setting->head, implode($setting->separator, $sql_setting));
        }
        else {
            $this->noneSection($setting);
        }
    }

    /**
     * build - build "where" section
     *
     * @return null
     */
    public function buildWhere()
    {
        $setting = $this->sequence->where;

        if (false === empty($this->temp_sql_array[$this->sections->where])) {
            $sql_setting = $this->temp_sql_array[$this->sections->where];
            $sql_array = array();

            foreach ($sql_setting as $key => $row) {
                if ('IN' === $row[1]) {
                    $parameters = array_pad(array() , count($this->temp_sql_array[$this->sections->value][$key]), $key);
                    $part_where = sprintf('%s %s (%s)', $row[0], $row[1], implode(',', $parameters));

                    $sql_array[] = $part_where;
                }
                else {
                    $sql_array[] = sprintf('%s %s %s', $row[0], $row[1], $key);
                }
            }

            $sql = sprintf('%s %s', $setting->head, implode($setting->separator, $sql_array));
            $this->temp_sql_statement_array[$this->sections->where] = $sql;
        }
        else {
            $this->noneSection($setting);
        }
    }

    /**
     * build - build "field" section
     *
     * @return null
     */
    public function buildField()
    {
        $setting = $this->sequence->field;

        if (true === empty($this->temp_sql_array[$this->sections->field])) {
            $this->temp_sql_array[$this->sections->field] = $this->fill_fields;
        }

        if (false === empty($this->temp_sql_array[$this->sections->field])) {
            $sql_setting = $this->temp_sql_array[$this->sections->field];
            $sql = sprintf('%s %s', $setting->head, implode($setting->separator, $sql_setting));
            $this->temp_sql_statement_array[$this->sections->field] = $sql;
        }
        else {
            $this->noneSection($setting);
        }
    }

    /**
     * build - build "limit" section
     *
     * @return null
     */
    public function buildLimit()
    {
        $setting = $this->sequence->limit;

        if (false === empty($this->temp_sql_array[$this->sections->limit])) {
            $sql_setting = $this->temp_sql_array[$this->sections->limit];
            $sql = sprintf('%s %s', $setting->head, implode($setting->separator, $sql_setting));
            $this->temp_sql_statement_array[$this->sections->limit] = $sql;
        }
        else {
            $this->noneSection($setting);
        }
    }

    /////////////
    // helpers //
    /////////////

    /**
     * clear - clear temp
     *
     * @return null
     */
    protected function clear()
    {
        $this->temp_sql_array = array();
        $this->temp_sql_statement_array = array();
    }

    /**
     * getConfig - get config
     *
     * @param array $where - hash array (key: field name, value: array(value, comparator))
     *
     * @return object - config object
     */
    protected function getConfig()
    {
        if (true === empty($this->config)) {
            $this->config = Config::model();
        }

        return $this->config;
    }

    /**
     * getSequence - get sequence
     *
     * @return object - config object
     */
    protected function getSequence()
    {
        $type = $this->dml_type;

        return $this->getConfig()->sequence->$type;
    }

    /**
     * wrapByGraveAccent - wrap by grave accent
     *
     * @param string $str - string that is wanna wrap by grave accent
     *
     * @return string
     */
    public function wrapByGraveAccent($str)
    {
        return preg_replace('/(\w+)([.]?)(\w*)/', '`\1`\2\3', $str);
    }

    /**
     * noneSection - doing with none section
     *
     * @param object $setting - section setting
     * @param string $message - message
     *
     * @return null
     */
    protected function noneSection($setting, $message = '')
    {
        if (false === empty($setting->required) && true === $setting->required) {
            throw new ModelException($this->exception->model->ex4002, $message);
        }
    }

    /**
     * setValue - set value for the parameter
     *
     * @param mixed $value - value of parameter
     *
     * @return string - parameter name
     */
    protected function setValue($value)
    {
        $name = '?' . md5(time() . rand());
        $this->temp_sql_array[$this->sections->value][$name] = $value;

        return $name;
    }
}
?>
