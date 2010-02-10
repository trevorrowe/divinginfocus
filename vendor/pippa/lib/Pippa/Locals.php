<?php

namespace Pippa;

class Locals implements \Iterator, \Countable, \ArrayAccess {

  protected static $locals;

  protected $data = array();

  protected function __construct($data = array()) {
    foreach($data as $key => $value)
      $this->offsetSet($key, $value);
  }

  public function __get($key) {
    return array_key_exists($key, $this->data) ? $this->data[$key] : null;
  }

  public function __set($key, $value) {
    $this->data[$key] = $value;
  }

  public function offsetExists($key) {
    return array_key_exists($key, $this->data);
  }

  public function offsetGet($key) {
    return $this->$key;
  }

  public function offsetSet($key, $value) {
    $this->$key = $value;
  }

  public function offsetUnset($key) {
    unset($this->data[$key]);
  }

  public function current() {
    return current($this->data); 
  }

  public function key() {
    return key($this->data);
  }

  public function next() {
    return next($this->data);
  }

  public function rewind() {
    return reset($this->data);  
  }

  public function valid() {
    return !is_null(key($this->data));
  }

  public function count() {
    return count($this->data);
  }

  public static function get() {
    if(is_null(self::$locals))
      self::$locals = new Locals();
    return self::$locals;
  }

}
