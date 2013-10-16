<?php

require('Helper.class.php');
/**
 * DB class
 * class creadted By SalmiGroup solutions company
 * it is used in revo CMS
 * @package revo
 * @copyright salmigroup 2012
 * @abstract 
 */
// db cons -------------------------------------------------------
define('DB_STATUS_CONNECTED', 1);
define('DB_STATUS_NOT_CONNECTED', 0);
// end db cons ---------------------------------------------------
// db cons -------------------------------------------------------
define('SQL_DIRECT', 'direct');
define('SQL_INSERT', 'insert');
define('SQL_UPDATE', 'update');
define('SQL_DELETE', 'delete');
define('SQL_GET', 'get');
define('SQL_COUNT', 'count');
define('SQL_MULTI', 'multi');
define('SQL_DROP_FIELD', 'drop_field');

// end db cons ---------------------------------------------------

/**
 * @uses rvObj

 */
class DB extends Base_class{

    const AS_ARRAY = 1;
    const AS_OBJECT = 2;
    const ACT_DONOTHING = 'do no thing';

    /** @var DB */
    public static $instance;
    public static $status = DB_STATUS_NOT_CONNECTED;
    private static $_link = null;
    private static $_host = '';
    private static $_user = '';
    private static $_password = '';
    private static $_db = '';
    public static $user_is_admin = FALSE;
    public static $success = 1;
    public static $get_as = 1;
    public static $limit = 0;
    public static $start = 0;
    public static $paged = 0;
    public static $sorted = 0;
    public static $last_error_no = 0;
    public static $show_errors = FALSE;
    public static $last_error = '';
    protected static $errors = array();
    public static $affected_rows = 0;
    public static $num_rows = 0;
    public static $last_id = 0;
    public static $last_id_in_table = array();
    public static $last_query = null;
    public static $result = null;
    public static $paginator = '';
    public static $query_count = 0;
    public static $last_multi_query_count = 0;
    public static $imported_queries = array();
    public static $queries = array(
        'insert' => array(),
        'update' => array(),
        'delete' => array(),
        'get' => array(),
        'multi' => array(),
    );

    private static function _format_condition(&$condition) {
        if (is_array($condition)) {
            $cond = array_shift($condition);
            $args = enclose_array_items($condition);
            $condition = vsprintf($cond, $args);
        }
    }

    private static function _set_last_id($table = '', $success = 1) {
        if ($success) {
            self::$last_id = mysql_insert_id();
        } else {
            self::$last_id = 0;
        }
        self::$last_id_in_table[$table] = self::$last_id;
    }

    private static function _theme_paginator($total_count, $_limit, $page = 0, $formId = '', $elarged = 0) {
        // set_hint('db - pagonator');
        /*
          if (is_callable('theme_paginator')){
          return theme_paginator($total_count, $_limit, $page, $formId, $elarged);
          }
         */
        if ($total_count <= $_limit)
            return '';
        $page = (isset($_GET['page'])) ? $_GET['page'] : 0;
        $request_array = array_merge($_REQUEST, $_GET, $_POST);
        unset($request_array['q']);
        unset($request_array['page']);
        $req = http_build_query($request_array);
        $req = $req ? "$req&" : '';
        $last = round($total_count / $_limit) - 1;
        $items = array();
        $i = 0;
        while ($i < ($total_count / $_limit)) {
            if ($i != $page)
                $items[] = html::a("?{$req}page=$i", $i + 1);
            else
                $items[] = html::span($i + 1);
            $i++;
        }

        $odd = 1;
        $out = "\n<ul class='paginator'>";
        foreach ($items as $index => $item) {
            $class = '';
            if ($index == count($items) - 1)
                $class = "last";
            if ($index == 0)
                $class = "first";
            $class .= ($odd > 0) ? " odd" : " even";
            $odd = - $odd;
            $out .= "\n\t<li id='$index' class='$class'>$item</li>";
        }
        $out .= "\n</ul>";

        // set_hint($out);
        return html::div(
                        $out
                        , array('class' => 'paginator')
        );
    }

    private static function _save_query($sql, $action, $errNo, $error) {
        $success = !($errNo);
        switch ($action) {
            case SQL_UPDATE :
                $code = 1;
                $result = self::get_affected_rows();
                break;
            case SQL_DELETE :
                $code = 2;
                $result = self::get_affected_rows();
                break;
            case SQL_INSERT :
                $code = 3;
                $result = self::get_last_id();
                break;
            case SQL_GET :
                $code = 4;
                $result = self::get_num_rows();
                break;
            default :
                $code = 0;
                $result = null;
        }
        $query = array(
            'sql' => str_replace("\n", ' ', $sql),
            'success' => $success,
            'errNo' => $errNo,
            'error' => $error,
            'result' => $result,
            'execTime' => time(),
        );
        self::$queries[$action][] = $query;
//        if(!$success)
//            tst($query);
        // showing errors
        if (!$success) {
            self::set_error("<b>($errNo) </b>$error", $code . '4', array('query'=>$sql));
        }
    }

    /**
     * The DB constructer will try to connect with the DB SERVER if parameters provided.
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $db
     * @return DB
     */
    private function __construct($host = '', $user = '', $password = '', $db = '') {
        if ("$host$user$password$db") {
            self::connect($host, $user, $password, $db);
        }
    }

    /**
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $db
     * @return DB
     */
    public static function init($host = '', $user = '', $password = '', $db = '') {
        if (empty(self::$instance)) {
            $obj = __CLASS__;
            self::$instance = new $obj($host, $user, $password, $db);
        }
        return self::$instance;
    }

    /**
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $db
     * @return boolean 
     */
    public static function connect($host, $user, $password, $db) {
        Tester::bookmark_event(__METHOD__, __FILE__, __LINE__);

        self::$_host = $host;
        self::$_user = $user;
        self::$_password = $password;
        self::$_db = $db;

        self::$_link = @mysql_pconnect($host, $user, $password);
        if (!self::$_link) {
            self::$status = DB_STATUS_NOT_CONNECTED;
            self::set_error('database connection could not be established.', '01', array('Address'=>"$user:$password@$host"));
        } else {

            self::$status = DB_STATUS_CONNECTED;
            self::set_charset('utf8');

            if (!mysql_select_db(self::$_db)) {
                self::$status = DB_STATUS_NOT_CONNECTED;
                self::set_error("Database don't exist", '02', array('Database name'=>self::$_db));
            }

            //
            Tester::bookmark_variable(__METHOD__, __LINE__, 'DB::status', self::$status);

            return self::$status;
        }
    }

    public static function set_charset($charset = 'utf8') {
        return mysql_set_charset('utf8');
    }

    public static function get_status() {
        return self::$status;
    }

    public static function get_host() {
        return self::_host;
    }

    public static function get_name() {
        return self::_db;
    }

    public static function get_username() {
        return self::_user;
    }

    public static function get_pass() {
        return self::_password;
    }

    public static function show_errors($show = TRUE) {
        self::$show_errors = $show;
    }

    public static function esc($str) {
        return mysql_real_escape_string(htmlspecialchars($str));
    }

    // QUERIES
    public static function multi_query($query) {
        $arr = $query;
        if (is_string($query))
            $arr = explode(";", $query);

        $queries = array();
        $query = '';
        while ($line = current($arr)) {
            $line = trim($line);
            if ($line != '' && substr($line, 0, 2) != '--' && substr($line, 0, 2) != '/*' && substr($line, 0, 1) != '#') {
                $query .= " $line";
                if (substr($line, -1) == ';') {
                    $queries[] = $query;
                    $query = '';
                }
            }
            next($arr);
        }
        tst($queries, '$queries');
        self::$last_multi_query_count = count($queries);
        self::$imported_queries = array_merge(self::$imported_queries, $queries);

        $result = TRUE;
        foreach ($queries as $query) {
//            tst($query);
            $result = ($result && (self::query($query, SQL_MULTI)));
        }
        return $result;
    }

    public static function direct_query($query) {
        return self::query($query, SQL_DIRECT);
    }

    public static function render_select_query($table, $fields = '*', $condition = NULL, $order_by = '', $limit = 0, $start = 0) {
        self::_format_condition($condition);

        $fields = (is_array($fields)) ? implode(', ', $fields) : $fields;
        $sql = "\nSELECT $fields ";
        $sql .= "\nFROM $table ";
        $sql .= ($condition) ? "\nWHERE $condition " : '';
        $sql .= ($order_by) ? "\nORDER BY $order_by " : "";
        $sql .= ($limit) ? "\nLIMIT $start, $limit" : "";
        //
        return $sql;
    }

    /**
     *
     * @param string $table
     * @param string $values
     * @param boolean $enclose_values
     * @return boolean 
     */
    public static function insert($table, $values, $enclose_values = FALSE, $on_duplicate_key = NULL) {
        foreach ($values as $field => $value) {
            $sql_fields[] = $field;
            $sql_values[] = ($enclose_values) ? Helper::enclose_var($value) : $value;
        }
        $sql_fields = implode(', ', $sql_fields);
        $sql_values = implode(', ', $sql_values);

        if ($on_duplicate_key) {
            switch ($on_duplicate_key) {
                case self::ACT_DONOTHING:
                    $on_duplicate_key = 'DO NO THING';
                    break;
                default:
//                    $on_duplicate_key = NULL;
                    break;
            }
            if ($on_duplicate_key)
                $on_duplicate_key = "ON DUPLICATE KEY $on_duplicate_key";
        }
        $sql = "INSERT INTO $table ($sql_fields) \nVALUES($sql_values) \n$on_duplicate_key;";
        // echo $sql;
        self::$last_id = 0;
        if (self::query($sql, SQL_INSERT)) {
            self::_set_last_id($table);
            return self::get_last_id();
        }

        return FALSE;
    }

    // UPDATE
    public static function update($table, $values, $condition = NULL, $enclose_values = FALSE) {
        self::_format_condition($condition);
        if ($enclose_values)
            $values = enclose_array_items($values);
        foreach ($values as $field => $value) {
            $update = " $field = $value";
            $updates[] = $update;
        }
        $sql_updates = implode(', ', $updates);
        $sql = "UPDATE $table \nSET $sql_updates ";
        $sql .= ($condition) ? "\n WHERE $condition " : '';

        self::$affected_rows = 0;
        if (self::query($sql, SQL_UPDATE)) {
            return self::get_affected_rows();
            // return 1;
        }
        return FALSE;
    }

    /**
     *
     * @param string $table
     * @param array $values
     * @param string $condition
     * @param booleab $enclose_values
     * @return boolean 
     */
    public static function update_or_insert($table, $values, $condition = NULL, $enclose_values = FALSE) { // this will duplicate non updated rows
        self::_format_condition($condition);
        if (!$result = self::update($table, $values, $condition, $enclose_values)) {
            if (!self::count($table, $condition)) {
                $result = self::insert($table, $values, $enclose_values);
                return $result;
            }
        } else {
            return $result;
        }
    }

    // DELETE
    public static function delete($table, $condition = NULL) {
        self::_format_condition($condition);
        $sql = "DELETE FROM $table ";
        $sql .= ($condition) ? "\nWHERE $condition " : '';
        self::$affected_rows = 0;
        if (self::query($sql, SQL_DELETE)) {
            return self::get_affected_rows();
        }
        return FALSE;
    }

    public static function get_last_id($table = '') {
        if ($table)
            return (array_key_exists($table, self::$last_id_in_table)) ? self::$last_id_in_table[$table] : 0;
        else
            return self::$last_id;
    }

    public static function get_num_rows() {
        self::$num_rows = (self::$result) ? mysql_num_rows(self::$result) : 0;
        return self::$num_rows;
    }

    public static function mysql_real_escape($str) {
        return mysql_real_escape_string($str);
    }

    // IMPORT / EXPORT
    public static function import_sql($SQLFile) {
        if ($sql = file($SQLFile)) {
            if (self::multi_query($sql)) {
                return TRUE;
            } else {
                self::set_error('sql import failed: at sql execution', '06');
                return FALSE;
            }
        } else {
            self::set_error('sql import failed: at file loading', '05');
            return TRUE;
        }
    }

    // TABLES MANIPULATION
    public static function drop_field($table, $field) {
        $sql = "ALTER TABLE $table DROP $field;";
        $result = self::query($sql, SQL_DROP_FIELD);
        return $result;
    }

    public static function empty_table($table) {
        $sql = "TRUNCATE TABLE $table;";
        return self::query($sql);
    }

    public function browse_table() {
        set_page_title(t('Describe database table', 'db'));
        $out = '';
        $limit = 30;
        $filter = '';
        $order = '';

        $result = mysql_list_tables(self::$link['db']);
        while ($raw = mysql_fetch_assoc($result)) {
            $tables[$raw['Tables_in_ruqaa']] = $raw['Tables_in_ruqaa'];
        }
        // $out .= printr($tables);
        $form = array(
            'options' => array(
                'id' => 'select-table-form',
                'method' => 'get',
                'submitLabel' => t('Select')
            ),
            'fields' => array(
                't' => array(
                    'type' => 'select',
                    'options' => $tables,
                    'label' => t('Select table'),
                )
            )
        );
        $out .= render_form($form);



        $table = get_request_var('t', 1, 'globals');
        if (!$lines = get_rows($table, '*', $filter, $order, $limit, 0, 1)) {
            $out .= theme_no_items();
        } else {
            // OUT

            $out .= theme_describe_table($lines);
            $total_count = count($table, $filter);
            $paginator = self::theme_paginator($total_count, $limit, 0, "paginator-" . rand(0, 1000));
            $out .= $paginator;
        }
        // out
        return $out;
    }

    public function query_to_table($quary) {
        $result = self::query($quary);
        return self::result_to_table($result);
    }

    public function result_to_table($result) {
        if ($result) {
            $tab = array();
            while ($raw = mysql_fetch_assoc($result)) {
                $tab[] = $raw;
            }
            return theme_describe_table($tab);
        } else {
            return 'no data';
        }
    }

    //-------------------------------------------------------------------------- STATIC FUNCTIONS
    public static function describe_table($table, $return_objects = FALSE) {
        if (!$result = self::query("DESCRIBE $table")) {
            return FALSE;
        }
        $num = mysql_num_rows($result);
        if (!$num)
            return FALSE;

        if ($return_objects)
            while ($raw = mysql_fetch_object($result)) {
                $tab[] = $raw;
            }
        else
            while ($raw = mysql_fetch_assoc($result)) {
                $tab[] = $raw;
            }
        // FREE MEMORY
        mysql_free_result($result);
        // RETURN
        return $tab;
    }

    public static function get_table_fields($table, $return_objects = FALSE) {
        if (!$result = self::query("SHOW FIELDS FROM `$table`")) {
            return FALSE;
        }
        $num = mysql_num_rows($result);
        if (!$num)
            return FALSE;

        if ($return_objects)
            while ($raw = mysql_fetch_object($result)) {
                $tab[] = $raw;
            }
        else
            while ($raw = mysql_fetch_assoc($result)) {
                $tab[] = $raw;
            }
        // FREE MEMORY
        mysql_free_result($result);
        // RETURN
        return $tab;
    }

    public static function get_table_keys($table, $return_objects = FALSE) {
        if (!$result = self::query("SHOW KEYS FROM `{$table}`")) {
            return FALSE;
        }
        $num = mysql_num_rows($result);
        if (!$num)
            return FALSE;

        if ($return_objects)
            while ($raw = mysql_fetch_object($result)) {
                $tab[] = $raw;
            }
        else
            while ($raw = mysql_fetch_assoc($result)) {
                $tab[] = $raw;
            }
        // FREE MEMORY
        mysql_free_result($result);
        // RETURN
        return $tab;
    }

    public static function list_tables() {
        if ($result = self::query('SHOW TABLES')) {
            while ($row = mysql_fetch_row($result)) {
                $tab[] = $row[0];
            }
            // FREE MEMORY
            mysql_free_result($result);
            // RETURN
            return $tab;
        }
        return NULL;
    }

    public static function get_affected_rows() {
        self::$affected_rows = mysql_affected_rows();
        return self::$affected_rows;
    }

    public static function get_error_no() {
        $errNo = mysql_errno();
        if ($errNo)
            self::$last_error_no = $errNo;
        return $errNo;
    }

    public static function get_error() {
        $error = mysql_error();
        if ($error) {
            self::$last_error = $error;
        }
        return $error;
    }

    /**
     *
     * @param strin $query
     * @param string $action
     * @return resouce (myqsl_result) FALSE on failure 
     */
    public static function query($query, $action = SQL_DIRECT) {
        if (!self::$status) {
            self::$set_error("Query to non connected db." . printr($query), '03');
            return FALSE;
        } else {
            self::$last_query = $query;
            self::$query_count++;
            self::$result = mysql_query($query);
            self::_save_query($query, $action, self::get_error_no(), self::get_error());
            if (!self::$result) {
                self::$success = 0;
                return FALSE;
            }
            self::$success = 1;
            return self::$result;
        }
    }

    /**
     *
     * @param string $table
     * @param mixte $condition
     * @return integer count, FALSE on failure 
     */
    public static function count($table, $condition = NULL) {
        self::_format_condition($condition);

        $sql = "SELECT count(*) as count FROM $table ";
        $sql .= ($condition) ? "WHERE $condition " : '';
        // file_log($sql);
        if ($result = self::query($sql, SQL_COUNT)) {
            $row = mysql_fetch_row($result);
            // FREE MEMORY
            mysql_free_result($result);
            return $row[0];
        }
        else
            return false;
    }

    public static function get_as($as = self::AS_ARRAY) {
        self::$get_as = $as;
    }

    /**
     *
     * @param type $table 
     * @param type $fields <p>default '*'.</p><p>JOINED fields are declared here</p>
     * @param type $condition <p>default ''.</p>
     * @param type $order_by <p>default ''.</p>
     * @param type $limit <p>default 0.</p>
     * @param type $start <p>default 0.</p>
     * @param type $paged <p>default 0.</p>
     * @param type $sorted <p>default ''.</p>
     * @return array when no records found return FALSE
     */
    public static function get_rows($table, $fields = '*', $condition = NULL, $order_by = '', $limit = 0, $start = 0, $paged = FALSE, $sorted = FALSE) {

        self::_format_condition($condition);

        self::$paged = $paged;
        self::$sorted = $sorted;

        // paged result
//        tst($table . $paged);
        if ($paged) {
            $page = (isset($_GET['page'])) ? $_GET['page'] : 0;
            if (is_numeric($page) && (int) $page > 0)
                $start = $page * $limit;
        }

        // theming paginator
        self::$paginator = '';
        if ($limit && $paged) {
            $count = self::count($table, $condition);
            self::$paginator = self::_theme_paginator($count, $limit);
//            tst(self::paginator);
        }
        // sorted result
        if ($sorted) {
            $order_by = rv_input_get_get('order', 0, $order_by);
        }

        // building SQL
        $fields = (is_array($fields)) ? implode(', ', $fields) : $fields;
        $sql = "\nSELECT $fields ";
        $sql .= "\nFROM $table ";
        $sql .= ($condition) ? "\nWHERE $condition " : '';
        $sql .= ($order_by) ? "\nORDER BY $order_by " : "";
        $sql .= ($limit) ? "\nLIMIT $start, $limit" : "";

        if (!$result = self::query($sql, SQL_GET)) {
            return false;
        }
        $num = mysql_num_rows($result);
        if (!$num)
            return false;

        $tab = array();
        if (self::$get_as == self::AS_OBJECT)
            while ($raw = mysql_fetch_object($result)) {
                $tab[] = $raw;
            }
        else
            while ($raw = mysql_fetch_assoc($result)) {
                $tab[] = $raw;
            }

        self::$get_as = self::AS_ARRAY;
        // FREE MEMORY
        mysql_free_result($result);
        // RETURN
        return $tab;
    }

    /**
     * @uses self::get_rows()
     * @param type $table
     * @param type $fields
     * @param type $condition
     * @param type $order_by
     * @return boolean 
     */
    public static function get_row($table, $fields, $condition = NULL, $order_by = '') {
        Tester::bookmark_variable(__METHOD__, __LINE__, 'DB::get_row arguments', func_get_args());

        self::$limit = $limit = 1;
        self::$start = $start = 0;
        if ($row = self::get_rows($table, $fields, $condition, $order_by, $limit, $start))
            return $row[0];
        else
            return false;
    }

    /**
     * @uses self::get_rows()
     * @param type $table
     * @param type $field
     * @param type $condition
     * @param type $order_by
     * @param type $limit
     * @param type $start
     * @param type $paged
     * @return type 
     */
    public static function get_values($table, $field, $condition = NULL, $order_by = '', $limit = 0, $start = 0, $paged = 0) {
        if ($rows = self::get_rows($table, $field, $condition, $order_by, $limit, $start, $paged)) {
            foreach ($rows as $row) {
                if (array_key_exists($field, $row))
                    $list[] = $row[$field];
            }
        }
        return (isset($list)) ? $list : FALSE;
    }

    /**
     * @uses self::get_row()
     * @param type $table
     * @param type $field
     * @param type $condition
     * @param type $order_by
     * @return boolean 
     */
    public static function get_value($table, $field, $condition = NULL, $order_by = '') {
        self::get_as(self::AS_ARRAY);
        if ($row = self::get_row($table, $field, $condition, $order_by))
            return $row[$field];
        else
            return false;
    }

    public static function get_last_query() {
        return self::$last_query;
    }

    public static function get_queries() {
        return self::$queries;
    }

    public static function get_query_count() {
        return self::$query_count;
    }

    public static function get_last_error() {
        return self::$last_error;
    }

    public static function get_paginator() {
        return self::$paginator;
    }

    /**
     *
     * @return array
     */
    public static function debug() {

        $debug = array(
            'connected' => self::$status == DB_STATUS_CONNECTED ? 1 : 0,
            'errors' => self::$errors,
            'query_count' => array(
                'total' => self::$query_count,
                'insert' => count(self::$queries['insert']),
                'update' => count(self::$queries['update']),
                'delete' => count(self::$queries['delete']),
                'get' => count(self::$queries['get']),
                'multi' => count(self::$queries['multi']),
            ),
            'queries' => self::$queries
        );

        return $debug;
    }

}

// end class
