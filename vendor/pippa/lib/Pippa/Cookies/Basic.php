<?php

namespace Pippa\Cookies;

class Basic implements \Iterator, \Countable, \ArrayAccess {

  protected $name;
  protected $expire;
  protected $path;
  protected $domain;
  protected $secure;
  protected $http_only;
  protected $data;

  public function __construct($name, 
    $expire = 0, $path = '', $domain = '', $secure = '', $http_only = '') 
  {

    $this->name = $name;
    $this->expire = $expire;
    $this->path = $path;
    $this->domain = $domain;
    $this->secure = $secure;
    $this->http_only = $http_only;

    $this->data = $this->exists() ? 
      $this->unmarshall($_COOKIE[$name]) : 
      array();
  }

  public function __set($key, $value) {
    $this->offsetSet($key, $value);
  }

  public function __get($key) {
    return $this->offsetGet($key);
  }

  public function __isset($key) {
    return isset($this->data[$key]);
  }

  public function __unset($key) {
    unset($this->data[$key]);
  }

  public function offsetExists($key) {
    return array_key_exists($key, $this->data);
  }

  public function offsetGet($key) {
    return array_key_exists($key, $this->data) ? $this->data[$key] : null;
  }

  public function offsetSet($key, $value) {
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

  public function as_array() {
    return $this->data;
  }

  public function exists() {
    return isset($_COOKIE[$this->name]);
  }

  public function clear() {
    $this->data = array();
    $this->save();
  }

  public function delete() {
    # to delete the cookie we blank out the data and set it to expire a day ago
    $this->data = array();
    $this->save(time() - 84600);
  }

  public function save($expire = null) {
    if(is_null($expire))
      $expire = $this->expire;
    setcookie($this->name, $this->marshall($this->data), $expire, $this->path, 
      $this->domain, $this->secure, $this->http_only);
  }

  protected function marshall($data) {
    return serialize($data);
  }

  protected function unmarshall($data) {
    return unserialize($data);
  }

}
