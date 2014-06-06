<?php
/**
 * class builder insert
 */
require_once 'class.builder-sql.php';
/**
 * class builder insert
 */
class Builder_Insert extends Builder_SQL
{
    protected $dml_type = 'insert';

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
        $args  = func_get_args();
        $value = (true === isset($args[1]) ? $args[1] : '');
        $name  = $this->setValue($value);

        if (false === $escape) {
            $field = $this->wrapByGraveAccent($field);
        }

        $this->temp_sql_array[$this->sections->field][$name] = $field;

        return $this;
    }

    /**
     * build - build "field" section
     *
     * @return null
     */
    public function buildField()
    {
        $setting = $this->sequence->field;

        if (false === empty($this->temp_sql_array[$this->sections->field])) {
            $sql_setting = $this->temp_sql_array[$this->sections->field];
            $sql = sprintf('(%s)', implode($setting->separator, $sql_setting));
            $this->temp_sql_statement_array[$this->sections->field] = $sql;
        }
        else {
            $this->noneSection($setting);
        }
    }

    /**
     * buildValue - build "value" section
     *
     * @return null
     */
    public function buildValue()
    {
        $setting = $this->sequence->value;

        if (false === empty($this->temp_sql_array[$this->sections->value])) {
            $sql_setting = $this->temp_sql_array[$this->sections->value];
            $parameter_marks = array_keys($sql_setting);
            $sql = sprintf('%s (%s)', $setting->head, implode($setting->separator, $parameter_marks));
            $this->temp_sql_statement_array[$this->sections->value] = $sql;
        }
        else {
            $this->noneSection($setting);
        }
    }
}
?>
