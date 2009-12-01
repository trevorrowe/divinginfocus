<?php

namespace Pippa;

class Request {

  protected $dispatched = false;
  protected $data;

  public function __construct($path, $method = 'GET', $params = array()) {

    # TODO : validate params, must be an array and may not contain
    #        controller or action (not allowed until dispatched)

    $this->data['path'] = trim($path, '/');
    $this->data['method'] = $method;
    $this->data['params'] = $params;
  }

  public function dispatched() {
    return $this->dispatched;
  }

  public function __get($what) {
    if(array_key_exists($what, $this->data))
      return $this->data[$what];
    throw new \Exception("Undefined Request property: $what");
  }

  public function dispatch($route_params) {
    $this->data['params'] = array_merge($this->data['params'], $route_params);
    $this->dispatched = true;
  }

  public static function get_http_request() {
    $parts = explode('.', $_SERVER['REDIRECT_URL']);
    $path = $parts[0];
    $params = $_REQUEST;
    if(isset($parts[1])) 
      $params['format'] = $parts[1];
    return new Request($path, $_SERVER['REQUEST_METHOD'], $params);
  }

}
