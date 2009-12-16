<?php

/**
 * Copyright (c) 2009 Trevor Rowe
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 */

# TODO : 0, '0', false, /false/i, /no/i should all evaluate to false

namespace Sculpt;

use PDO;
use PDOException;
use DateTime;

function connect($dsn) {
  Sculpt::$connection = MySQLConnection::connect($dsn);
}

function connection() {
  return Sculpt::$connection;
}

function is_assoc($var) {
  return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
}

# Configuration happens here:
#
#   Sculpt::$logger = $my_logger;
#
class Sculpt {

  public static $per_page = 10;

  public static $connection;

  public static $logger;

  public static function log($query, $seconds) {
    # TODO : allow toggling color on / off
    if(self::$logger) {
      static $i = 0;
      static $colors = array("\x1b[36m","\x1b[35m");
      static $weights = array("\x1b[1m", '');
      $reset = "\x1b[0m";
      $bold = "\x1b[1m";
      $color = $colors[$i % 2];
      $weight = $weights[$i % 2];
      $time = self::format_seconds($seconds);
      $msg = "$color{$bold}[Sculpt] ($time)$reset$weight $query$reset";
      self::$logger->log($msg);
      $i += 1;
    }
  }

  protected static function format_seconds($seconds) {
    $milli = round($seconds * 10000.0) / 10.0;
    switch(true) {
      case $milli < 1000: 
        return sprintf("%.1fms", $milli); 
      case $milli < (1000 * 60): 
        return sprintf("%.2f seconds", $milli / 1000); 
      default:
        $mins = floor(($milli / 1000) / 60);
        $seconds = ($milli - $mins * 1000 * 60) / 1000;
        return sprintf("%d mins %.2f seconds", $mins, $seconds);
    }
  }

}

### connection classes

abstract class AbstractConnection {

  protected $c;

  public static function connect($dsn) {

    $url = @parse_url($dsn);

    if(!isset($url['host'])) {
      $msg = 'Database host must be specified in the connection string.';
      throw new Exception($msg);
    }

    $info = new \stdClass();
    $info->protocol = $url['scheme'];
    $info->host     = $url['host'];
    $info->db       = isset($url['path']) ? substr($url['path'],1) : null;
    $info->user     = isset($url['user']) ? $url['user'] : null;
    $info->pass     = isset($url['pass']) ? $url['pass'] : null;

    if(isset($url['port']))
      $info->port = $url['port'];

    return new MySQLConnection($info);
  }

  protected function __construct($info) {

    $dsn = "$info->protocol:host=$info->host" . 
      (isset($info->port) ? ";port=$info->port" : '') .	
      ";dbname=$info->db";

    $opts = array(
      PDO::ATTR_CASE => PDO::CASE_LOWER,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
      PDO::ATTR_STRINGIFY_FETCHES  => false,
    );

    $this->c = new PDO($dsn, $info->user, $info->pass, $opts);

  } 

  public function query($sql, &$values = array()) {
    $start = microtime(true);
    try {
      $sth = $this->c->prepare($sql);
    } catch(PDOException $e) {
      throw new Exception("PREPARE FAILED: $sql");
    }
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    try {
      $sth->execute($values);
    } catch(PDOException $e) {
      throw new Exception("EXECUTE FAILED: $sql");
    }
    Sculpt::log($sql, microtime(true) - $start);
    return $sth;
  }

  public function insert_id($sequence = null) {
    return $this->c->lastInsertId($sequence);
  }
  
  abstract public function tables();

  abstract public function columns($table_name);

}

abstract class AbstractColumn {

  const STRING   = 1;
  const INTEGER  = 2;
  const DECIMAL  = 3;
  const DATETIME = 4;
  const DATE     = 5;
  const BOOLEAN  = 6;

  abstract public function name();

  abstract public function type();

  abstract public function default_value();

  public function cast($value) {

    $type = $this->type();

    if($value === null)
      return null;

    switch($type) {
      case self::STRING:   return (string) $value;
      case self::INTEGER:  return (int) $value;
      case self::BOOLEAN:  return (boolean) $value;
      case self::DECIMAL:  return (double) $value;
      case self::DATETIME:
      case self::DATE:
        if($value instanceof DateTime)
          return $value;
        $value = date_create($value);
        $errors = \DateTime::getLastErrors();
        if ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
          return null;
        return $value;
    }
  }

}

class MySQLConnection extends AbstractConnection {

  public function quote_name($name) {
    return "`$name`";
  }

  public function tables() {
    $tables = array();
    $sth = $this->query('SHOW TABLES');
    while($row = $sth->fetch(PDO::FETCH_NUM))
      $tables[] = $row[0];
    return $tables;
  }

  public function columns($table) {
    $columns = array();
    $sth = $this->query("SHOW COLUMNS FROM $table");
    while($row = $sth->fetch()) {
      $column = new MySQLColumn($row);
      $columns[$column->name()] = $column;
    }
    return $columns;
  }

}

class MySQLColumn extends AbstractColumn {

  protected $name;

  protected $type;

  protected $default_value;

  protected static $type_mappings = array(
    'tinyint(1)' => self::BOOLEAN,
    'datetime'   => self::DATETIME,
    'timestamp'  => self::DATETIME,
    'date'       => self::DATE,
    'int'        => self::INTEGER,
    'tinyint'    => self::INTEGER,
    'smallint'   => self::INTEGER,
    'mediumint'  => self::INTEGER,
    'bigint'     => self::INTEGER,
    'float'      => self::DECIMAL,
    'double'     => self::DECIMAL,
    'numeric'    => self::DECIMAL,
    'decimal'    => self::DECIMAL,
    'dec'        => self::DECIMAL,
  );

  public function __construct($column_details) {

    $this->name = $column_details['field'];

    $raw_type = $column_details['type'];
    if(isset(self::$type_mappings[$raw_type]))
      $this->type = self::$type_mappings[$raw_type];
    else {
      preg_match('/^(.*?)\(([0-9]+(,[0-9]+)?)\)/', $raw_type, $matches);
      if(sizeof($matches) > 0 && isset(self::$type_mappings[$matches[1]]))
        $this->type = self::$type_mappings[$matches[1]];
      else
        $this->type = self::STRING;
    }

    $default = $column_details['default'];
    $this->default_value = $default === '' ? null : $this->cast($default);

  }

  public function name() {
    return $this->name;
  }

  public function type() {
    return $this->type;
  }

  public function default_value() {
    return $this->default_value;
  }

}

### Exceptions

class Exception extends \Exception {}

class RecordInvalidException extends Exception {
  public function __construct($obj) {
    parent::__construct("Validation failed: $obj->errors");
  }
}

class NonExistantAttributeException extends Exception {
  public function __construct($class, $attr_name) {
    $msg = "$class class: undefined attribute setter called: $attr_name";
    parent::__construct($msg);
  }
}

class NonWhitelistedAttributeBulkAssigned extends Exception {
  public function __construct($class, $attr_name) {
    $msg = "$class class: non-whitelisted attribute `$attr_name` bulk assigned";
    parent::__construct($msg);
  }
}

class BlacklistedAttributeBulkAssigned extends Exception {
  public function __construct($class, $attr_name) {
    $msg = "$class class: blacklisted attribute `$attr_name` bulk assigned";
    parent::__construct($msg);
  }
}

### tables

class Table {

  private static $cache = array();

  public $name;
  public $class;
  public $columns;
  public $connection;

  public static function get($class_name) {
    if(!isset(self::$cache[$class_name]))
      self::$cache[$class_name] = new Table($class_name);
    return self::$cache[$class_name];
  }

  protected function __construct($class) {
    
    $this->class = $class;

    # TODO : just a temporary hack, make this better
    $this->connection = connection();

    $this->name = isset($class::$table_name) ?
      $class::$table_name :
      strtolower($class) . 's';
      # TODO : use a string inflector to create a table name

    $this->columns = $this->connection->columns($this->name);

  }

  # TODO : move to the abstract connection classs?
  public function select($opts = array()) {

    $bind_params = array();

    $sql = $this->select_fragment($opts);
    $sql .= $this->from_fragment($opts);
    $sql .= $this->where_fragment($opts, $bind_params);

    #if(isset($parts['joins']))
    #  $sql .= " WHERE {$parts['joins']}";

    if(!empty($opts['group'])) {
      $sql .= " GROUP BY {$opts['group']}";
      if(isset($opts['having']))
        $sql .= " HAVING {$opts['having']}";
    }

    if(isset($opts['order']))
      $sql .= " ORDER BY {$opts['order']}";

    if(isset($opts['limit']))
      $sql .= " LIMIT {$opts['limit']}";

    if(isset($opts['offset']))
      $sql .= " OFFSET {$opts['offset']}";

    $objects = array();
    $sth = $this->connection->query($sql, $bind_params);
    $class = $this->class;
    while($row = $sth->fetch()) {
      $objects[] = $class::hydrate($row);
    }
    return $objects;
  }

  private function select_fragment($opts) {
    if(isset($opts['select']))
      $select = $opts['select'];
    else
      $select = '*';
    return "SELECT $select";
  }

  private function from_fragment($opts) {
    $from = isset($parts['from']) ? $parts['from'] : $this->name;
    return " FROM $from";
  }

  private function where_fragment($opts, &$bind_params) {

    if(empty($opts['where']))
      return '';

    $conditions = array();
    foreach($opts['where'] as $where) {
      switch(true) {

        # 'admin' => array('admin' => true)
        case is_assoc($where):
          $condition = array();
          foreach($where as $col_name => $col_value)
            $condition[] = "$col_name = ?";
          $condition = implode(' AND ', $condition);
          $bind_params = array_merge($bind_params, array_values($where));
          break;

        # 'admin' => array('admin = ?', true)
        case is_array($where):
          $condition = array_shift($where);
          $bind_params = array_merge($bind_params, $where);
          break;

        # admin => 'admin = 1'
        case is_string($where):
          $condition = $where;
          break;

        default:
          throw new Exception("invalid where condition");

      }
      $conditions[] = "($condition)";
    }
    return " WHERE " . implode(' AND ', $conditions);
  }

  # TODO : public function insert() {}

  # TODO : public function update() {}

  # TODO : public function delete() {}

}

class Scope {

  private $table;

  private $sql_parts = array(
    'select' => null,
    'from'   => null,
    'where'  => array(),
    'joins'  => array(),
    'group'  => null,
    'having' => null,
    'order'  => null,
    'limit'  => null,
    'offset' => null,
  );

  public function __construct($table) {
    $this->table = $table;
  }

  public function from($table_name) {
    $this->sql_parts['from'] = $table_name;
    return $this;
  }

  public function select($select_sql_fragment) {
    $this->sql_parts['select'] = $select_sql_fragment;
    return $this;
  }

  public function where($where) {
    $args = func_get_args();
    if(count($args) == 1)
      $this->sql_parts['where'][] = $where;
    else
      $this->sql_parts['where'][] = $args;
    return $this;
  }

  public function joins($joins) {
    if(is_array($joins))
      $this->sql_parts['joins'] = array_merge($this->sql_parts['joins'], $joins);
    else
      $this->sql_parts['joins'][] = $joins;
    return $this;
  }

  public function group_by($group_by_sql_fragment) {
    $this->sql_parts['group'] = $group_by_sql_fragment;
    return $this;
  }

  public function having($having_sql_fragment) {
    $this->sql_parts['having'] = $having_sql_fragment;
    return $this;
  }

  public function order($order_sql_fragment) {
    $this->sql_parts['order'] = $order_sql_fragment;
    return $this;
  }

  public function limit($limit) {
    $this->sql_parts['limit'] = $limit;
    return $this;
  }

  public function offset($offset) {
    $this->sql_parts['offset'] = $offset;
    return $this;
  }

  public function __get($scope) {
    $this->$scope();
    return $this;
  }

  public function __call($method, $args) {

    $class = $this->table->class;

    # static class scope (e.g. User::$scopes['admin'])
    if(isset($class::$scopes[$method])) {
      $this->apply_static_scope($class::$scopes[$method]);
      return $this;
    }

    # dynamic class scope (e.g. User::admin_scope($scope))
    $static_method = "{$method}_scope";
    if(method_exists($class, $static_method)) {
      $class::$static_method($this);
      return $this;
    }

    # sorting (ascend or decend by a single column)
    # TODO : raise an exception if not a valid column
    if(preg_match('/(asc|ascend|desc|descend)_by_(.+)/', $method, $matches)) {
      $dir = $matches[1] == 'asc' || $matches[1] == 'ascend' ? 'ASC' : 'DESC';
      $this->order("{$matches[2]} $dir");
      return $this;
    }

    # auto-magical scopes id_is, age_lte, etc
    $columns = array_keys($this->table->columns);
    $regex = '/(' . implode('|', $columns) . ')_(.+)/';
    if(preg_match($regex, $method, $matches)) {
      $col = $matches[1];
      switch($matches[2]) {
        case 'equals':
        case 'is':
          $this->where("$col = ?", $args[0]);
          return $this;
          break;
        case 'does_not_equal':
        case 'not_equal_to':
        case 'is_not':
      }
    }

    #equals, is
    #does_not_equal, is_not
    #begins_with
    #not_begin_with
    #end_with
    #not_end_with
    #like
    #no_like
    #greater_than
    #greater_than_or_equal_to
    #less_than
    #less_than_or_equal_to
    #null
    #not_null
    #blank
    #??? not_blank

    throw new Exception("unknown scope $class::$method");
  }

  protected function apply_static_scope($static_scope) {
    foreach($static_scope as $key => $value)
      if(is_numeric($key)) # some_other_scope
        $this->$value;
      else # where
        $this->$key($value);
  }

  #public function get() {
  #  $obj = $this->first();
  #  if(is_null($obj))
  #    throw new Exception("object not found");
  #  return $obj;
  #}

  # TODO : public function count() {}

  public function first() {
    $results = $this->limit(1)->all();
    return empty($results) ? null : $results[0];
  }

  public function all() {
    return $this->table->select($this->sql_parts);
  }

  public function paginate($page, $per_page = null) {
    # TODO : grab default per_page from a configuration
    if(is_null($per_page))
      $per_page = Sculpt::$per_page;
    $this->limit($per_page)->offset(($page - 1) * $per_page);
    $objects = $this->all();
    return $objects;
  }

  # TODO : public function each($callback) {}
  # TODO : public function batch($callback, $size = 500) {}

}

class Collection {

  public $page;
  public $per_page;
  public $total;

  private $objects;

  public function __construct($objects, $page, $per_page, $total) {
    $this->objects = $objects;
    $this->page = $page;
    $this->per_page = $per_page;
    $this->total = $total;
  }

  # TODO : implement iterator interface

}

### models

abstract class Model {

  static $attr_accessors = array();
  static $attr_whitelist = array();
  static $attr_blacklist = array();

  static $scopes = array();

  public $errors;

  protected $class;
  protected $table;

  private $attr = array();

  public function __construct($attributes = null) {

    $this->class = get_called_class();
    $this->table = Table::get($this->class);
    $this->errors = new Errors();

    # set default values for this object based on column defaults
    foreach($this->table->columns as $attr_name => $column)
      $this->attr[$attr_name] = $column->default_value();

    if(!is_null($attributes))
      $this->set_attributes($attributes);
  }

  public static function hydrate($attributes) {
    $class = get_called_class();
    $obj = new $class();
    foreach($attributes as $attr_name => $attr_value)
      $obj->_set($attr_name, $attr_value);
    return $obj;
  }

  public function is_new_record() {
    return !is_null($this->attr['id']);
  }

  public function __get($attr_name) {
    return $this->attribute($attr_name);
  }

  public function __set($attr_name, $attr_value) {
    $this->set_attribute($attr_name, $attr_value);
  }

  public function attribute_before_type_cast($name) {
    return $this->_get($name, false);
  }

  public function attribute($name) {
    $getter = "_$name";
    if(method_exists($this, $getter))
      return $this->$getter();
    else
      return $this->_get($name);
  }

  public function set_attribute($name, $value) {
    $setter = "_set_$name";
    if(method_exists($this, $setter))
      $this->$setter($value);
    else
      $this->_set($name, $value);
  }

  protected function _get($name, $type_cast = true) {
    $this->ensure_attr_defined($name);
    $value = isset($this->attr[$name]) ? $this->attr[$name] : null;
    if(isset($this->table->columns[$name]) && $type_cast) {
      $value = $this->table->columns[$name]->cast($value);
    }
    return $value;
  }

  protected function _set($name, $value) {
    $this->ensure_attr_defined($name);
    if(isset($this->table->columns[$name])) {
      # TODO : track changes for dirty tracking
    }
    $this->attr[$name] = $value;
  }
  
  protected function ensure_attr_defined($name) {
    if(!isset($this->table->columns[$name]) && 
       !in_array($name, static::$attr_accessors)) 
    {
      throw new NonExistantAttributeException($this->class, $name);
    }
  }

  protected function bulk_assign($attributes) {
    if(empty(static::$attr_whitelist) && empty(static::$attr_blacklist)) {
      # no whitelist or blacklist
      foreach($attributes as $name => $value)
        $this->set_attribute($name, $value);
    } else if(!empty(static::$attr_whitelist)) {
      # whitelisting
      foreach($attributes as $name => $value) {
        if(in_array($name, static::$attr_whitelist))
          $this->set_attribute($name, $value);
        else
          throw new NonWhitelistedAttributeBulkAssigned($this->class, $name);
      }
    } else {
      # blacklisting
      foreach($attributes as $name => $value) {
        if(in_array($name, static::$attr_blacklist))
          throw new BlacklistedAttributeBulkAssigned($this->class, $name);
        else
          $this->set_attribute($name, $value);
      }
    }
  }

  public function attributes() {
    $attributes = array();
    foreach(array_keys($this->attr) as $attr_name)
      $attributes[$attr_name] = $this->attribute($attr_name);
    return $attributes;
  }

  public function set_attributes($attributes) {
    $this->bulk_assign($attributes);
  }

  public function update_attributes($attributes) {
    $this->set_attributes($attributes);
    return $this->save();
  }

  public function create() {
    if($this->validate()) {
      $this->table->insert($this);
      return true;
    } else {
      return true;
    }
  }

  public function save() {
    if($this->is_new_record()) {
      return $this->create();
    } else {
      if($this->validate()) {
        $this->table->update($this);
        return true;
      } else {
        return false;
      }
    }
  }

  public function force_save() {
    if(!$this->save())
      throw new RecordInvalidException($this);
  }

  public function validate() {
    $this->errors->clear();
    # TODO : run validations
    return $this->errors->is_empty();
  }

  public function to_param() {
    return $this->_get('id');
  }

  ### class methods

  # returns a scope
  public static function find($opts = array()) {
    return new Scope(Table::get(get_called_class()));
  }

  # TODO : allow this function to recieve an array of ids as well
  public static function get($id) {
    $scope = new Scope(Table::get(get_called_class()));
    $obj = $scope->id_is($id)->first();
    if(is_null($obj))
      throw new Exception("exceptional find - didn't find it");
    else
      return $obj;
  }

  public static function table_name() {
    return Table::get(get_called_class())->name;
  }

  public static function columns() {
    return Table::get(get_called_class())->columns;
  }

}

### Errors

class Errors {

  protected $msgs = array();

  public function add_to_base($msg) {
    $this->add(null, $msg);
  }

  public function add($to, $msg) {
    if(isset($this->msgs[$to]))
      array_push($this->msgs[$to], $msg);
    else
      $this->msgs[$to] = array($msg);
  }

  public function on($attr) {
    return isset($this->msgs[$attr]) ? $this->msgs[$attr] : null;
  }

  public function on_base() {
    return $this->on(null);
  }

  public function count() {
    return count($this->msgs);
  }

  public function is_empty() {
    return empty($this->msgs);
  }

  public function is_invalid($attr) {
    return isset($this->msgs[$attr]);
  }

  public function full_messages() {

    if(empty($this->msgs))
      return array();

    $messages = array();
    $this->each_full_message(function($message) use (&$messages) {
      array_push($messages, $message);
    });
    return $messages;
  }

  public function each_message($callback) {
    foreach($this->msgs as $on => $messages)
      foreach($messages as $message)
        $callback($on, $message);
  }

  public function each_full_message($callback) {
    foreach($this->msgs as $on => $messages)
      foreach($messages as $message)
        $callback($this->expand_message($on, $message));
  }

  protected function expand_message($on, $msg) {
    # TODO : $on needs to be titleized
    return trim("$on $msg");
  }

  public function clear() {
    $this->msgs = array();
  }

  public function __toString() {
    return implode(', ', $this->full_messages());
  }

  # TODO : public function to_xml() {}
  # TODO : public function to_json() {}
  # TODO : implement iterator interface, should iterate through full_messages
}
