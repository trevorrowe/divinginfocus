<?php

namespace Pippa;

class Params implements \Iterator, \Countable, \ArrayAccess {
  
  protected $data = array();

  public function __construct($data = array()) {
    foreach($data as $key => $value)
      $this->offsetSet($key, $value);
  }

  public function __get($key) {
    return $this->offsetGet($key);
  }

  public function offsetExists($key) {
    return array_key_exists($key, $this->data);
  }

  public function offsetGet($key) {
    return array_key_exists($key, $this->data) ? $this->data[$key] : null;
  }

  public function offsetSet($key, $value) {
    if(is_array($value))
      $this->data[$key] = new Params($value);
    else if(is_numeric($value) && is_int($value))
      $this->data[$key] = (int) $value;
    else if(is_numeric($value) && is_float($value))
      $this->data[$key] = (float) $value;
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

  protected function to_string($array) {
    $values = array();
    foreach($array as $k => $v) {
      if(is_object($v) && get_class($v) == 'Pippa\Params') {
        $values[] = "'$k' => " . $this->to_string($v);
      } else if(is_numeric($v)) {
        $values[] = "'$k' => {$v}";
      } else {
        $v = str_replace('\\', '\\\\', $v);
        $v = str_replace('\'', '\\\'', $v);
        $values[] = "'$k' => '$v'";
      }
    }
    $values = implode(', ', $values);
    return "array($values)";
  }

  public function __toString() {
    return $this->to_string($this->data);
  }

  public function count() {
    return count($this->data);
  }

}
