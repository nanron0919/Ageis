<?php
/**
 * class model
 */
require_once FRAMEWORK_ROOT . '/_core/sql-builder/class.builder-select.php';

/**
 * class model
 */
abstract class Model extends Builder_Select
{
    /**
     * consts of field
     */
    const FIELD_TYPE     = 0;
    const FIELD_RANGE    = 1;
    const FIELD_REQUIRED = 2;

    /**
     * consts of range
     */
    const RANGE_MAX  = 0;
    const RANGE_MIN  = 1;

    /**
     * consts of what it has
     */
    const HAS_MODEL    = 0;
    const HAS_SRC_KEY  = 1;
    const HAS_DEST_KEY = 2;
    const HAS_FILTER   = 3;

    /**
     * exception config
     */
    protected $exception;

    /**
     * db connection
     */
    protected $db;

    /**
     * table attributes
     */
    protected $table         = '';       // table name
    protected $primary_key   = '';       // primary key
    /////////////////////////////////////////////
    // fields:                                 //
    // array([type], [max], [min], [required]) //
    /////////////////////////////////////////////
    protected $fields        = array();  // all fields including type
    protected $store_fields  = array();  // the fields that inserted
    protected $hasA          = array();  // set what the model that has own
    protected $fill_fields   = array();  // default return anyway (declare at parent class)
    protected $ingore_fields = array();  // dosen't return from query anyway
    protected $ingore_update = array();  // dosen't update anyway

    /**
     * helper objects
     */
    public $sql_builder;

    /**
     * temporary
     */
    protected $sql = array();   // contains every part of sql statement

    /**
     * costructor
     *
     * @param object - db driver
     */
    public function __construct($db_driver)
    {
        // $this->env         = Config::env();
        $this->exception   = Config::exception();
        $this->fill_fields = array($this->table . '.*');

        // check required settings are ready
        $this->checkSettings();

        // setting db connection by driver
        $this->db = $db_driver;

        parent::__construct();
    }

    /**
     * getAttribute - get attribute
     *
     * @param string $name - attribute name
     *
     * @return mixed - value of attibute
     */
    public function getAttribute($name)
    {
        return (true === property_exists($this, $name) ? $this->$name : '');
    }

    /**
     * appendField - append field
     *
     * @param string $field - field name
     *
     * @return object - this
     */
    public function appendField($name)
    {
        $this->fill_fields = (true === is_array($this->fill_fields) ? $this->fill_fields : array());
        $this->fill_fields[] = $name;

        return $this;
    }

    ////////////////////////
    // fetch data methods //
    ////////////////////////

    /**
     * findByKey - find by key
     *
     * @param mixed $val - value for key field
     *
     * @return object - hash array into an object
     */
    public function findByKey($val)
    {
        $sql_builder = $this->from($this->table)->where($this->primary_key, $val);

        return $this->fetchResult($sql_builder);
    }

    /**
     * get - get result
     *
     * @return object - hash array into an object
     */
    public function get()
    {
        return $this->fetchResult($this);
    }

    /**
     * first - return first result
     *
     * @return object
     */
    public function first()
    {
        $rows = $this->fetchResult($this->limit(0, 1));
        $row = new stdClass;

        if (0 < count($rows)) {
            $row = $rows[0];
        }

        return $row;
    }

    /**
     * count - fetch count of result
     *
     * @return int
     */
    public function count()
    {
        $field = sprintf('COUNT(%s) AS cnt', $this->primary_key);
        $result = call_user_func_array(
            array($this->db, 'query'),
            $this->field($field, true)->from($this->table)->build()
        );
        return (int) (false === empty($result[0]->cnt) ? $result[0]->cnt : 0);
    }

    /**
     * fetchResult - fetch result
     *
     * @param object $sql_builder - builder
     *
     * @return object
     */
    protected function fetchResult($sql_builder)
    {
        $result = call_user_func_array(
            array($this->db, 'query'),
            $sql_builder->from($this->table)->build()
        );
        $result = $this->getHasA($result);

        return Converter::arrayToObject(false !== $result ? $this->ingoreFields($result) : new stdClass);
    }

    /**
     * ingoreFields - ingore fields
     *
     * @param array &$result - result
     *
     * @return array
     */
    protected function ingoreFields($result)
    {
        foreach ($result as $key => &$row) {
            foreach ($this->ingore_fields as $field) {
                if (true === array_key_exists($field, $row)) {
                    unset($row->$field);
                }
            }
        }

        return $result;
    }

    ////////////////////////////
    // setting data associate //
    ////////////////////////////

    /**
     * hasA - has a object
     *
     * @param string $model   - model object
     * @param string $srcKey  - which field as source key
     * @param string $destKey - which field as destination key
     * @param string $name    - hash key
     * @param array  $filter  - filter
     *
     * @return this
     */
    public function hasA($model, $srcKey, $destKey, $name, $filter = array())
    {
        if (true === $this->equals($model)) {
            $this->hasA[$name] = array($model, $srcKey, $destKey, $filter);
        }
        else {
            throw new ModelException($this->exception->model->ex4003);
        }

        return $this;
    }

    /////////////////
    // modify data //
    /////////////////

    /**
     * saving - execute before save
     *
     * @return null
     */
    protected function saving()
    {
        // do something before save
    }

    /**
     * save - insert a new entry/update for this table
     *
     * @return int - affect rows
     */
    public function save()
    {
        // execute before save
        $this->saving();

        $affect_rows = 0;
        $key_value = '';

        if (false === empty($this->store_fields[$this->primary_key])) {
            $where = array(
                $this->primary_key => array($this->store_fields[$this->primary_key])
            );
            // temporary save value of primary key
            $key_value = $where[$this->primary_key];
            unset($this->store_fields[$this->primary_key]);
            $affect_rows = $this->update($this->store_fields, $where);

            // set back value of primary key
            $this->$$this->primary_key = $key_value;
        }
        else {
            $this->insert($this->store_fields);
            $affect_rows = 1;
        }

        // execute after save
        $this->saved();

        // clear
        $this->store_fields = array();

        return $affect_rows;
    }

    /**
     * saved - execute after save
     *
     * @return null
     */
    protected function saved()
    {
        // do something after save
    }

    /**
     * updating - execute before update
     *
     * @return null
     */
    protected function updating()
    {
        // do something before update
    }

    /**
     * update - update an entry
     *
     * @param array $fields - hash array for updating fields
     * @param array $where  - hash array for conditions
     *
     * @return int
     */
    protected function update($fields, $where)
    {
        // do it at begining
        $this->updating();

        $update = new Builder_Update;

        $temp_update = $update->from($this->table);

        foreach ($fields as $key => $value) {
            // pass by ignore updating fields
            if (false === array_key_exists($key, $this->ingore_update)) {
                $temp_update = $temp_update->field($key, $value);
            }
        }

        foreach ($where as $key => $condition) {
            $args = array_merge(array($key), $condition);
            $temp_update = call_user_func_array(array($temp_update, 'where'), $args);
        }

        $result = $temp_update->build();

        $update_result = call_user_func_array(array($this->db, 'query'), $result);

        // do it at the end
        $this->updated();

        return $update_result;
    }

    /**
     * updated - execute after update
     *
     * @return null
     */
    protected function updated()
    {
        // do something after update
    }

    /**
     * inserting - execute before insert
     *
     * @return null
     */
    protected function inserting()
    {
        // do something before insert
    }

    /**
     * insert - insert an entry
     *
     * @param array $fields - hash array for insert fields
     *
     * @return int
     */
    protected function insert($fields)
    {
        // do it before insert
        $this->inserting();

        $insert = new Builder_Insert;

        $temp_insert = $insert->from($this->table);

        foreach ($fields as $key => $value) {
            $temp_insert = $temp_insert->field($key, $value);
        }

        $result = $temp_insert->build();

        $insert_result = call_user_func_array(array($this->db, 'query'), $result);

        // do it after insert
        $this->inserted();

        return $insert_result;
    }

    /**
     * inserted - execute after insert
     *
     * @return null
     */
    protected function inserted()
    {
        // do something after insert
    }

    /**
     * equals - equals
     *
     * @param object $model - model object
     *
     * @return boolean
     */
    public function equals($model)
    {
        return (true === is_object($model) && get_class() === get_parent_class($model));
    }

    ////////////
    // setter //
    ////////////

    /**
     * addIngoreField - add ingore field
     *
     * @param array $fields - fields name
     *
     * @return this
     */
    public function addIngoreFields($fields)
    {
        foreach ($fields as $field) {
            if (false === in_array($field, $this->ingore_fields)) {
                $this->ingore_fields[] = $field;
            }
        }

        return $this;
    }

    /**
     * __set - set field and value for the new entry
     *
     * @param string $name - field's name
     * @param mixed  $val  - value
     *
     * @return null
     */
    public function __set($name, $val)
    {
        if (true === array_key_exists($name, $this->fields)) {
            // uses strict comparison here
            $field    = $this->fields[$name];
            $type     = $field[self::FIELD_TYPE];
            $range    = (true === isset($field[self::FIELD_RANGE]) ? $field[self::FIELD_RANGE] : array());
            $func     = array('Validator', 'is' . $type);
            $required = (true === isset($field[self::FIELD_REQUIRED]) ? $field[self::FIELD_REQUIRED] : false);
            $args     = array($val, $range, $required);
            $code     = call_user_func_array($func, $args);

            if (0 === $code) {
                $this->store_fields[$name] = $val;
            }
            else {
                $error = 'ex' . $code;

                throw new DataTypeException($this->exception->datatype->$error);
            }
        }
        else {
            throw new DataTypeException($this->exception->database->ex3001);
        }
    }

    ////////////
    // getter //
    ////////////

    /**
     * __get - set field and value for the new entry
     *
     * @param string $name - field's name
     * @param mixed  $val  - value
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (true === array_key_exists($name, $this->fields)) {
            return $this->store_fields[$name];
        }
        else {
            throw new DataTypeException($this->exception->datatype->$error);
        }
    }

    ////////////////////
    // helper methods //
    ////////////////////

    /**
     * checkSettings - check settings
     *
     * @return null
     */
    protected function checkSettings()
    {
        if (true === empty($this->table)
            || true === empty($this->primary_key)
            || true === empty($this->fields)
        ) {
            throw new ModelException($this->exception->model->ex4001);
        }
    }

    ///////////////////////////////////
    // build associate model methods //
    ///////////////////////////////////

    /**
     * fetchResult - fetch result
     *
     * @param object $result - result
     *
     * @return array
     */
    protected function getHasA($result)
    {
        foreach ($this->hasA as $key => $has) {
            $model = $has[self::HAS_MODEL];
            $filter = $has[self::HAS_FILTER];

            foreach ($result as &$row) {
                $model = $this->buildAssocModel($model, $filter);
                $src_key  = $has[self::HAS_SRC_KEY];
                $dest_key = $has[self::HAS_DEST_KEY];

                if (false === empty($row->$src_key)) {
                    $row->$key = $model
                            ->addIngoreFields(array($dest_key))
                            ->where($dest_key, $row->$src_key)
                            ->get();
                }
                else {
                    $row->$key = array();
                }
            }
        }

        return $result;
    }

    /**
     * buildAssocModel - build an associate model
     *
     * @param object $model  - model
     * @param array  $filter - filter
     *
     * @return object
     */
    protected function buildAssocModel($model, $filter)
    {
        foreach ($filter as $row) {
            $model->where($row[0], $row[1]);
        }

        return $model;
    }
}
?>