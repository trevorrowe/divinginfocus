<?php

### flashes

function flash() {
  $args = func_get_args();
  switch(func_num_args()) {
    case 0:
      return \Pippa\Flash::$data;
      break;
    case 1:
      return \Pippa\Flash::get($args[0]);
      break;
    case 2:
      \Pippa\Flash::set($args[0], $args[1]);
      break;
    default:
      throw new Exception('invalid args');
  }
}

function flash_now($key, $payload) {
  \Pippa\Flash::set($key, $payload, true);
}

### string inflectors

function camelize($lower_case_and_underscored_word) {
  $str = $lower_case_and_underscored_word;
  return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
}

function capitalize($word) {
  return ucfirst(strtolower($word));
}

function classify($word) {
  # TODO : should this return the actual class instead? I'm leaning that way
  return camelize(singularize($word));
}

function dasherize($word) {
  return str_replace('_', '-', underscore($word));
}

function foreign_key($class_name) {
  return underscore(demodulize($class_name)) . "_id";
}

function humanize($word) {
  if(count(\Pippa\Inflect::$humans) > 0) {
    $original = $word;   
    foreach(\Pippa\Inflect::$human as $rule) {
      list($regex, $replace) = $rule;
      $str = preg_replace($regex, $replace, $word);
      if($original != $str) break;
    }	
  }
  return capitalize(str_replace(array('_','_id'), array(' ',''), $word));
}

function ordinalize($num) {
  $num = intval($num);
  if(in_array(($num % 100), range(11, 13))) {
    $num .= 'th';
  } else {
    switch(($num % 10)) {
      case 1:
        $num .= 'st';
        break;
      case 2:
        $num .= 'nd';
        break;
      case 3:
        $num .= 'rd';
        break;
      default:
        $num .= 'th';
    }    
  }
  return $num;
}

function pluralize($singular, $count = 0, $plural = null) {

  if($count == 1)
    return $singular;

  if(!is_null($plural))
    return $plural;

  if(isset(\Pippa\Inflect::$cache['plural'][$singular]))
    return \Pippa\Inflect::$cache['plural'][$singular];

  if(in_array($singular, \Pippa\Inflect::$uncountable))
    return $singular;

  foreach(\Pippa\Inflect::$plural as $rule) {
    list($regex, $replace) = $rule;
    if(preg_match($regex, $singular)) {
      $plural = preg_replace($regex, $replace, $singular);
      \Pippa\Inflect::$cache['plural'][$singular] = $plural;
      return $plural;
    }	
  }

  return $singular;
}

function singularize($plural) {

  if(isset(\Pippa\Inflect::$cache['singular'][$plural]))
    return \Pippa\Inflect::$cache['singular'][$plural];

  if(in_array($plural, \Pippa\Inflect::$uncountable))
    return $plural;

  foreach(\Pippa\Inflect::$singular as $rule) {
    list($regex, $replace) = $rule;
    if(preg_match($regex, $plural)) {
      $singular = preg_replace($regex, $replace, $plural);
      \Pippa\Inflect::$cache['singular'][$plural] = $singular;
      return $singular;
    }	
  }

  return $plural;
}

function tableize($class_name) {
  return pluralize(underscore($class_name));
}

function titleize($word) {
  return ucwords(humanize(underscore($$word)));
}

function underscore($camel_cased_word) {
  $str = $camel_cased_word;
  $str = str_replace('::', '/', $str);
  $str = preg_replace('/([A-Z]+)([A-Z])/', '\1_\2', $str);
  return strtolower(preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $str));
}

### url

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

### view helpers

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

### routing

function route($pattern, $options = array()) {
  array_push(\Pippa\App::$routes, new \Pippa\Route($pattern, $options));
}

### miscellany

function debug($obj, $stop = true) {
  echo '<pre>';
  #var_dump($obj);
  print_r($obj);
  echo "</pre>\n";
  if($stop)
    exit();
}

### format helpers

function format_bytes($bytes, $precision = 2) {
  $kb = 1024.0;
  $mb = 1048576.0;
  $gb = 1073741824.0;
  $tb = 1099511627776.0;
  $pb = 1125899906842624.0;
  $eb = 1152921504606846976.0;
  $zb = 1180591620717411303424.0;
  $yb = 1208925819614629174706176.0;
  switch(true) {
    case $bytes < $kb:
      return sprintf("%d Bytes", $bytes);
    case $bytes < $mb:
      return sprintf("%.{$precision}f KB", $bytes / $kb);
    case $bytes < $gb:
      return sprintf("%.{$precision}f MB", $bytes / $mb);
    case $bytes < $tb:
      return sprintf("%.{$precision}f GB", $bytes / $gb);
    case $byte < $pb:
      return sprintf("%.{$precision}f TB", $bytes / $tb);
    case $byte < $eb:
      return sprintf("%.{$precision}f PB", $bytes / $pb);
    case $byte < $zb:
      return sprintf("%.{$precision}f EB", $bytes / $eb);
    case $byte < $yb:
      return sprintf("%.{$precision}f ZB", $bytes / $zb);
    default:
      return sprintf("%.{$precision}f YB", $bytes / $yb);
  }
}

function h($str) {
}
