<?php

namespace Pippa;

class LocalsContainer {

  private $_locals;

  public function __construct() {
    $this->_locals = Locals::get();
  }

  public function __call($method, $args) {
    return Helper::invoke($method, $args);
  }

  public function &__get($key) {
    if(isset($this->_locals->$key))
      $value =& $this->_locals->$key;
    else
      $value = null;
    return $value;
  }

  public function __set($name, $value) {
    $this->_locals->$name = $value;
  }

  public function __isset($key) {
    return isset($this->_locals->$key);
  }

  public function __unset($key) {
    unset($this->_locals->$key);
  }

  public function locals() {
    return $this->_locals;
  }

}
