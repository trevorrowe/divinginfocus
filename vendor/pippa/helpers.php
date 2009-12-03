<?php

namespace Pippa;

# TODO : move this function from helpers (maybe to a different namespace?)
function route($pattern, $options = array()) {
  Router::add_route($pattern, $options);
}

function url($params) {

  # strings get passed through unmofied, assumed to already be urls
  if(is_string($params))
    return $params;

  foreach(Router::$routes as $route)
    if($route->testParams($params))
      return $route->buildPath($params);

  $msg = "Unable to build a url from the passwed route params: ";
  throw new Exception($msg . print_r($params, true));

}

# TODO : make this use FirePHP instead
function debug($obj, $stop = true) {
  echo '<pre>';
  #var_dump($obj);
  print_r($obj);
  echo "</pre>\n";
  if($stop)
    exit();
}

function str_begins_with($str, $search) {
  return (strncmp($str, $search, strlen($search)) == 0);
}

function cycle() {

  static $indexes = array();

  $t = debug_backtrace();
  $key = "{$t[0]['file']}|{$t[0]['line']}";

  if(!isset($indexes[$key]))
    $indexes[$key] = 0;

  $argc = func_num_args();
  $args = func_get_args();

  $value = $args[$indexes[$key] % $argc];

  $indexes[$key] += 1;
  return $value;

}

function request_url() {
  static $url;
  if(!$url) {
    $s = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ?  's' : '';
    $domain = $_SERVER['SERVER_NAME'];
    $port = $_SERVER['SERVER_PORT'];
    $port = $port == 80 || $port == 442 ? '' : ":$port";
    $uri = $_SERVER['REQUEST_URI'];
    $url = "http$s://$domain$port$uri";
  }
  return $url;
}
