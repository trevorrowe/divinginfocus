<?php

namespace Pippa\Cookies;

class Basic extends \Pippa\MagicHash {

  protected $name;
  protected $expire;
  protected $path;
  protected $domain;
  protected $secure;
  protected $http_only;
  protected $data;

  public function __construct($name, 
    $expire = 0, $path = '/', $domain = '', $secure = '', $http_only = '') 
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
