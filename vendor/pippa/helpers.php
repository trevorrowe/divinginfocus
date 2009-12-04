<?php

namespace Pippa;

# Examples:
# 
#   url('photos', 'show', 123);
#   url($photo);
#
# controller = CURRENT_CONTROLLER
# action = new
#
#   url('new');  
#   url(array('action' => 'new'));
#
# controller = CURRENT_CONTROLLER
# action = edit
# id = 123
#
#   url('edit', $photo);
#   url(array('action' => 'edit', 'id' => 123));
#
# controller = profiles
# action = show
# id = 456
#
#   url('profiles', 'show', 456);
#   url(array('controller' => 'profiles', 'action' => 'show', 'id' => 456));
#
# controller = home
#
#   url(array('controller' => 'home'));
#
# Returns all of the following w/out modification:
#
#   url('/logout');
#   url('https://foo.com');
#   url('http://foo.com');
#   url('ftp://bar.com');
#
function url() {

  $argc = func_num_args();
  $args = func_get_args();

  # single arguments that start with a / or a protocol (like http://) are
  # returned unmofied as they are already valid urls
  if($argc == 1 && !is_array($args[0]))
    if($args[0][0] == '/' || preg_match('#^[a-z]+://#', $args[0]))
      return $args[0];

  $params = array();
  switch($argc) {
    case 1:
      $params = is_array($args[0]) ? $args[0] : array('action' => $args[0]);
      break;
    case 2:
      $params = array(
        'action' => $args[0],
        'id' => $args[1]
      );
      break;
    case 3:
      $params = array(
        'controller' => $args[0],
        'action' => $args[1],
        'id' => $args[2]
      );
      break;
    default:
      throw new Exception('invalid number of arguments for url');
  }

  if(!isset($params['controller']))
    $params['controller'] = Request::get_http_request()->params['controller'];

  if(!isset($params['action']))
    $params['action'] = 'index';

  if(isset($params['id']) && is_object($params['id']))
    $params['id'] = $params['id']->to_param();

  foreach(App::$routes as $route)
    if($route->matches_params($params))
      return $route->build_url($params);

  $msg = "Unable to build a url from: ";
  throw new Exception($msg . print_r($args, true));

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

# TODO : move this function from helpers (maybe to a different namespace?)
function route($pattern, $options = array()) {
  App::add_route($pattern, $options);
}
