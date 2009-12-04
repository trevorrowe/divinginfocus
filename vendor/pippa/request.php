<?php

namespace Pippa;

class Request {

  protected $data;

  public function __construct($uri, $opts = array()) {

    $defaults = array(
      'protocol' => 'http',
      'host'     => 'localhost',
      'port'     => 80,
      'method'   => 'GET',
      'time'     => time(),
      'params'   => array(),
    );

    foreach($defaults as $k => $default) {
      if(isset($opts[$k]))
        $this->data[$k] = $opts[$k];
      else
        $this->data[$k] = $default;
    }

    $parts = explode('?', $uri);
    $parts = explode('.', $parts[0]);
    $this->data['route_path'] = ltrim($parts[0], '/');

    if(isset($parts[1])) {
      $this->data['format'] = $parts[1];
      $this->data['params']['format'] = $parts[1];
    } else {
      $this->data['format'] = 'html';
    }

    $this->data['uri'] = $uri;
    $this->data['url'] = $this->data['protocol']."://{$this->data['host']}$uri";

  }

  public function &__get($what) {
    if(isset($this->data[$what]))
      return $this->data[$what];
    throw new Exception("Undefined Request property: $what");
  }

  public function dispatch($route_params) {
    $this->data['params'] = array_merge($this->data['params'], $route_params);
  }

  public static function &get_http_request() {
    static $http_request;
    if(!$http_request) {
      $http_request = new Request($_SERVER['REQUEST_URI'], array(
        'protocol' => 'http',
        'host'     => $_SERVER['HTTP_HOST'],
        'port'     => $_SERVER['SERVER_PORT'],
        'method'   => $_SERVER['REQUEST_METHOD'],
        'time'     => $_SERVER['REQUEST_TIME'],
        'params'   => $_REQUEST,
      ));
    }
    return $http_request;
  }

}
