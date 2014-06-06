<?php
/**
 * class builder update
 */
require_once 'class.builder-sql.php';
/**
 * class builder update
 */
class Builder_Update extends Builder_SQL
{
    protected $dml_type = 'update';

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
            $sql = $setting->head;

            foreach ($sql_setting as $key => $val) {
                $sql .= sprintf(' %s = %s', $val, $key);
            }

            $this->temp_sql_statement_array[$this->sections->field] = $sql;
        }
        else {
            $this->noneSection($setting);
        }
    }
}


?>
