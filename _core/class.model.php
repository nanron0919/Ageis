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
     * consts of prop
     */
    const PROP_DATA_TYPE = 0;
    const PROP_RANGE     = 1;
    const PROP_REQUIRED  = 2;
    const PROP_DEFAULT   = 3;

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
     * pagination
     */
    protected $is_pagination     = false;    // setup pagination with limit
    protected $config_pagination = null; // config pagination

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
        $this->exception   = Config::exception();
        $this->fill_fields = array($this->table . '.*');

        // check required settings are ready
        $this->checkSettings();

        // setting db connection by driver
        $this->db = $db_driver;

        // setting pagination config as object
        $this->config_pagination = new stdClass;

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
        $this->from($this->table)->where($this->primary_key, $val);
        $result = $this->first();

        return $result;
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
     * hash - get hash result
     *
     * @param string $hashkey - hash key
     *
     * @return object - hash array into an object
     */
    public function hash($hashkey)
    {
        $rows = $this->fetchResult($this);
        $result = new stdClass;

        foreach ($rows as $key => $row) {
            $hashkey = (
                false === empty($row->$hashkey)
                ? $row->$hashkey
                : $key
            );

            $result->$hashkey = $row;
        }

        return $result;
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

        if (true === $this->is_pagination) {
            if (false === empty($result[$this->config_pagination->has_prev_page_idx])) {
                unset($result[$this->config_pagination->has_prev_page_idx]);
            }
            else {
                $this->config_pagination->prev_page_idx = 0;
            }

            if (false === empty($result[$this->config_pagination->has_next_page_idx])) {
                unset($result[$this->config_pagination->has_next_page_idx]);
            }
            else {
                $this->config_pagination->next_page_idx = 0;
            }
        }

        return Converter::arrayToObject(
            false !== $result ? array_values($this->ingoreFields($result)) : array()
        );
    }

    /**
     * pagination
     *
     * @param int $curr_page - current page
     * @param int $limit     - limit
     *
     * @return object - this
     */
    public function pagination($page, $limit)
    {
        // setup pagination on next query.
        $this->is_pagination = true;
        $this->config_pagination->curr_page = $page;    // set current page index.
        $this->config_pagination->has_prev_page_idx = -1;    // if it exists its has prev page.
        $this->config_pagination->has_next_page_idx = 0;    // if it exists its has next page.
        $this->config_pagination->prev_page_idx = $page - 1;    // default prev page index.
        $this->config_pagination->next_page_idx = $page + 1;    // default next page index.
        $start = $page - 1;

        if (1 < $page) {
            $this->config_pagination->has_prev_page_idx = 0;
            $start = ($start * $limit - 1);
            $limit = $limit + 2;
        }
        else {
            // retrieve the next of the end page.
            $limit = $limit + 1;
        }

        if (true === $this->is_pagination) {
            $this->config_pagination->has_next_page_idx = $limit - 1;
        }

        $this->limit($start, $limit);

        return $this;
    }

    /**
     * get previous page
     *
     * @return int - 0 is no previous page
     */
    public function getPrevPageIndex()
    {
        return (
            false === empty($this->config_pagination->prev_page_idx)
            ? $this->config_pagination->prev_page_idx
            : 0
        );
    }

    /**
     * get next page
     *
     * @return int - 0 is no next page
     */
    public function getNextPageIndex()
    {
        return (
            false === empty($this->config_pagination->next_page_idx)
            ? $this->config_pagination->next_page_idx
            : 0
        );
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

    /**
     * join - join
     *
     * @param string $join_model  - what model that joined
     * @param array  $join_fields - join fields
     * @param string $direction   - inner, left or right (default by INNER JOIN)
     *
     * @return this
     */
    public function join($join_model, $join_fields = array(), $direction = 'INNER JOIN')
    {
        if (true === $this->equals($join_model)) {
            $ref_fields = array(
                'from' => '',
                'to' => '',
            );

            $ref_fields['from'] = (
                false === empty($join_fields['from'])
                ? $join_fields['from']
                : $this->getTableName() . '.' . $this->primary_key
            );
            $ref_fields['to'] = (
                false === empty($join_fields['to'])
                ? $join_fields['to']
                : $join_model->getTableName() . '.' . $join_model->getPrimaryKey()
            );

            parent::join($join_model->getTableName(), $ref_fields, $direction);
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
    public function saving()
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
        if (empty($this->store_fields)) {
            // TODO: throw an exception
        }

        // execute before save
        $this->saving();

        $primary_key = $this->primary_key;
        $affect_rows = 0;
        $key_value = '';

        try {
            // try insert first
            $this->insert($this->store_fields);
            $affect_rows = 1;
        }
        catch (Exception $e) {
            // if insert fail do update.
            if (false === empty($this->store_fields[$primary_key])) {
                $key_value = $this->store_fields[$primary_key];
                $where = array(
                    $primary_key => array($key_value)
                );

                unset($this->store_fields[$primary_key]);
                $affect_rows = $this->update($this->store_fields, $where);
                $this->store_fields[$primary_key] = $key_value;
            }
        }

        // execute after save
        $this->saved();

        // clear stored values
        $this->clearStoredValues();

        return $affect_rows;
    }

    /**
     * saved - execute after save
     *
     * @return null
     */
    public function saved()
    {
        // do something after save
    }

    /**
     * updating - execute before update
     *
     * @return null
     */
    public function updating()
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
    public function update($fields, $where)
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
    public function updated()
    {
        // do something after update
    }

    /**
     * inserting - execute before insert
     *
     * @return null
     */
    public function inserting()
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
    public function insert($fields)
    {
        // do it before insert
        $this->inserting();

        // set default from setting
        $fields = $this->setDefaultValueFromSetting($fields);

        $insert = new Builder_Insert;

        $temp_insert = $insert->from($this->table);

        foreach ($fields as $key => $value) {
            $temp_insert = $temp_insert->field($key, $value);
        }

        $result = $temp_insert->build();

        $insert_result = call_user_func_array(array($this->db, 'query'), $result);

        // do it after insert
        $this->inserted();

        return $this->insertId();
    }

    /**
     * inserted - execute after insert
     *
     * @return null
     */
    public function inserted()
    {
        // do something after insert
    }

    /**
     * deleting - execute before delete
     *
     * @return null
     */
    public function deleting()
    {
        // do something before delete
    }

    /**
     * delete - delete an entry
     *
     * @param array $conditions - hash array for delete entry with conditions
     *
     * @return bool
     */
    public function delete($conditions)
    {
        // do it before delete
        $this->deleting();

        $delete = new Builder_Delete;

        $temp_delete = $delete->from($this->table);

        foreach ($conditions as $key => $value) {
            $temp_delete->where($key, $value);
        }

        $result = $temp_delete->build();

        $delete_result = call_user_func_array(array($this->db, 'query'), $result);

        // do it after delete
        $this->deleted();

        return (0 < $this->db->affectedRows());
    }

    /**
     * deleted - execute after delete
     *
     * @return null
     */
    public function deleted()
    {
        // do something after delete
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
     * setDefaultValueBySetting - set default from setting
     *
     * @param array $fields - fields
     *
     * @return array
     */
    protected function setDefaultValueFromSetting($fields)
    {
        foreach ($this->fields as $key => &$field) {
            if (true === empty($fields[$key]) && true === isset($field[self::PROP_DEFAULT])) {
                $fields[$key] = $field[self::PROP_DEFAULT];
            }
        }

        return $fields;
    }

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
     * get insert id
     *
     * @return int
     */
    public function insertId()
    {
        return (true === method_exists($this->db, 'insertId') ? $this->db->insertId() : '');
    }

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
            return (
                true === isset($this->store_fields[$name])
                ? $this->store_fields[$name]
                : ''
            );
        }
        else {
            throw new DataTypeException($this->exception->database->ex3001);
        }
    }

    /**
     * getEmptyEntry - get an empty entry not includes primary key
     *
     * @return object
     */
    public function getEmptyEntry()
    {
        $entry = new stdClass;

        foreach ($this->fields as $name => $prop) {
            if ($name !== $this->primary_key) {
                if (true === isset($prop[self::PROP_DEFAULT])) {
                    $entry->$name = $prop[self::PROP_DEFAULT];
                }
                else {
                    switch ($prop[self::PROP_DATA_TYPE]) {
                    case 'int':
                    case 'double':
                    case 'float':
                        $entry->$name = 0;
                        break;
                    case 'string':
                        $entry->$name = '';
                        break;
                    default:
                        break;
                    }
                }
            }
        }

        return $entry;
    }

    /**
     * get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * get primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    ////////////////////
    // helper methods //
    ////////////////////

    /**
     * clear all stored field but primary key
     *
     * @return null
     */
    public function clearStoredValues()
    {
        $this->store_fields = array();
    }

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