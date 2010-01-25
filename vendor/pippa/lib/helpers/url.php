<?php

function url() {
 
  # TODO : provide a 'relative' option
  # TODO : provide an 'anchor' option
  # TODO : provide a 'port' option
  # TODO : provide a 'protocol' option

  $argc = func_num_args();
  $args = func_get_args();

  #$options = array('only_path', 'anchor', 'host', 'protocol', 'port');

  # get the options hash from the end of the passed arguments
  if($argc > 1 && is_assoc($args[$argc - 1])) {
    $opts = array_pop($args);
    $argc -= 1;
  } else {
    $opts = array();
  }

  # When this function is called with a single argument that is a string
  # that looks like '/some/url/path' or 'http://someurl.com', we will
  # return that url unmodified.  These need no transformation.
  if($argc == 1 && is_string($args[0]))
    if($args[0][0] == '/' || preg_match('#^[a-z]+://#', $args[0]))
      return $args[0];

  switch($argc) {
    case 1:
      if(is_assoc($args[0]))
        $params = $args[0];
      else if(is_array($args[0]))
        return call_user_func_array('url', $args[0]);
      else
        $params = array('action' => $args[0]);
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
    $params['controller'] = \Pippa\Request::get_http_request()->params['controller'];

  if(!isset($params['action']))
    $params['action'] = 'index';

  if(isset($params['id']) && is_object($params['id']))
    $params['id'] = $params['id']->to_param();

  $url_path = null;
  foreach(\Pippa\App::$routes as $route) {
    if($route->matches_params($params)) {
      $url_path = $route->build_url($params);
      break;
    }
  }

  if(is_null($url_path)) {
    $msg = "Unable to build a url from: ";
    throw new Exception($msg . print_r($args, true));
  }

  # TODO : use options to add things like protocol, anchor, etc

  return $url_path;

}
