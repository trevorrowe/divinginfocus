<?php

namespace Pippa;

# TODO : provide a read-only mode (useful for request params)
class MagicHash implements \Iterator, \Countable, \ArrayAccess {
  
  protected $data = array();

  public function __construct($data = array()) {
    $this->merge($data);
  }

  public function __set($key, $value) {
    $this->offsetSet($key, $value);
  }

  public function __get($key) {
    return $this->offsetGet($key);
  }

  public function __isset($key) {
    return $this->valid($key);
  }

  public function __unset($key) {
    $this->offsetUnset($key);
  }

  public function __toString() {
    return $this->to_string($this->data);
  }

  public function offsetExists($key) {
    return array_key_exists($key, $this->data);
  }

  public function offsetGet($key) {
    return array_key_exists($key, $this->data) ? $this->data[$key] : null;
  }

  public function offsetSet($key, $value) {
    if(is_array($value))
      $this->data[$key] = new MagicHash($value);
    else
      $this->data[$key] = $value;
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

  public function merge($data) {
    foreach($data as $key => $value)
      $this->offsetSet($key, $value);
    return $this;
  }

  public function to_array() {
    $array = Array();
    foreach($this->data as $k => $v) {
      if(is_a($v, '\Pippa\MagicHash'))
        $array[$k] = $v->to_array();
      else
        $array[$k] = $v;
    }
    return $this->data;
  }

  protected function to_string($array) {
    $values = array();
    foreach($array as $k => $v) {
      if(is_object($v)) {
        if(is_a($v, 'Pippa\MagicHash'))
          $values[] = "'$k' => " . $this->to_string($v);
        else
          $values[] = "'$k' => " . (string) $v;
      } else if(!is_string($v) && is_numeric($v)) {
        $values[] = "'$k' => $v";
      } else {
        $v = str_replace('\\', '\\\\', $v);
        $v = str_replace('\'', '\\\'', $v);
        $values[] = "'$k' => '$v'";
      }
    }
    $values = implode(', ', $values);
    return "array($values)";
  }

}
