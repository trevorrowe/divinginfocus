<?php

namespace Pippa;

class Request {

  public $url;

  public $uri;

  public $protocol;

  public $host;

  public $port;

  public $method;

  public $time;

  public $format;

  public $params;

  public $routeable_path;

  public function __construct($uri, $opts = array()) {

    $defaults = array(
      'uri'      => $uri,
      'protocol' => 'http',
      'host'     => 'localhost',
      'port'     => 80,
      'method'   => 'GET',
      'time'     => time(),
      'params'   => new Params(),
    );

    foreach($defaults as $k => $default) {
      if(isset($opts[$k]))
        $this->$k = $opts[$k];
      else
        $this->$k = $default;
    }

    if(is_array($this->params))
      $this->params = new Params($this->params);

    $parts = explode('?', $uri);
    $parts = explode('.', $parts[0]);
    $this->routeable_path = trim($parts[0], '/');

    if(isset($parts[1])) {
      $this->format = $parts[1];
      $this->params['format'] = $parts[1];
    } else {
      $this->format = 'html';
    }

    $this->uri = $uri;
    $this->url = $this->protocol . "://{$this->host}$uri";

  }

  public function is_get() {
    return $this->method == 'GET';
  }

  public function is_post() {
    return $this->method == 'GET';
  }

  public function dispatch($route_params) {
    $this->params = array_merge($this->params, $route_params);
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
