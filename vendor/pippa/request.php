<?php

namespace Pippa;

class Request {

  public $path;
  public $method;
  public $params;

  public function __construct($path, $method = 'GET', $params = array()) {
    $this->path = trim($path, '/');
    $this->method = $method;
    $this->params = $params;
  }

  public static function httpRequest() {
    $parts = explode('.', $_SERVER['REDIRECT_URL']);
    $path = $parts[0];
    $params = $_REQUEST;
    return new Request($path, $_SERVER['REQUEST_METHOD'], $params);
  }

}
