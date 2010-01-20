<?php

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

function flash_messages($levels = null) {

  if(is_null($levels)) 
    $levels = array('error', 'warn', 'notice', 'info');

  $flashes = array();
  foreach($levels as $level) {
    if($msg = flash($level)) {
      if(is_array($msg))
        $msg = tag('ul', collect($msg, function($k, $v) { return "<li>$v</li>"; }));
      else
        $msg = tag('p', $msg);
      $flashes[] = tag('div', $msg, array('class' => "$level flash"));
    }
  }

  return empty($flashes) ? 
    null : 
    tag('div', $flashes, array('id' => 'flashes'));

}

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

function format_y_n($bool) {
  if(is_null($bool))
    return '';
  return $bool ? 'Y' : 'N';
}

function format_yes_no($bool) {
  if(is_null($bool))
    return '';
  return $bool ? 'Yes' : 'No';
}

function format_date($date, $format = '%Y-%m-%d') {
  if(is_null($date))
    return '';
  return strftime($format, $date->getTimestamp());
}

function format_datetime($datetime, $format = '%Y-%m-%d %T') {
  if(is_null($datetime))
    return '';
  return strftime($format, $datetime->getTimestamp());
}

## tag helpers

function text_field_tag($name, $value = null, $opts = array()) {
  $opts['type'] = 'text';
  $opts['name'] = $name;
  $opts['value'] = $value;
  append_css_class_to_tag_opts($opts, 'text');
  if(!array_key_exists('id', $opts))
    $opts['id'] = form_field_dom_id($name);
  return tag('input', null, $opts);
}

function password_field_tag($name, $value = null, $opts = array()) {
  $opts['type'] = 'password';
  $opts['name'] = $name;
  $opts['value'] = $value;
  append_css_class_to_tag_opts($opts, 'text');
  if(!array_key_exists('id', $opts))
    $opts['id'] = form_field_dom_id($name);
  return tag('input', null, $opts);
}

function form_field_dom_id($form_field_name) {
  return preg_replace('/\[(.*)\]/', '_$1', $form_field_name);
}

function append_css_class_to_tag_opts(&$opts, $class) {
  if(isset($opts['class'])) {
    if(!preg_match("/(^|\s+){$opts['class']}(\s+|$)/", $opts['class']))
      $opts['class'] = "{$opts['class']} $class";
  } else {
    $opts['class'] = $class;
  }
}

## form field helpers

function text_field($obj, $attr, $opts = array()) {
  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  return text_field_tag($name, $value, $opts);
}

function password_field($obj, $attr, $opts = array()) {
  $name = form_field_name($obj, $attr);
  $value = $obj->attribute_before_type_cast($attr);
  return password_field_tag($name, $value, $opts);
}

function checkbox_field($obj, $attr, $opts = array()) {

  $id = isset($opts['id']) ? $opts['id'] : form_field_id($obj, $attr);
  $name = form_field_name($obj, $attr);
  $checked = $obj->$attr ? 'checked' : null;

  $hidden = tag('input', null, array(
    'type' => 'hidden',
    'name' => $name,
    'value' => '0',
    'class' => 'hidden',
  ));

  $checkbox = tag('input', null, array(
    'type' => 'checkbox',
    'id' => $id,
    'name' => $name,
    'value' => '1',
    'checked' => $checked,
    'class' => 'checkbox',
  ));

  return $hidden . $checkbox;
}

function form_field_name($obj, $attr) {
  return strtolower(get_class($obj) . "[$attr]");
}








function form_label($obj, $attr, $opts = array()) {

  $title = form_field_title($obj, $attr);

  if(isset($opts['required']) && $opts['required'])
    $title = tag('span', '*', array('class' => 'required_symbol')) . $title;
    
  return tag('label', $title, array(
    'for' => form_field_id($obj, $attr),
  ));
}

function form_field($type, $obj, $attr, $opts = array()) {
  $name = '';
  $value = '';
  switch($type) {
    case 'text':
      return text_field($obj, $attr, $opts);
    case 'checkbox':
      return checkbox_field($obj, $attr, $opts);
    case 'password':
      return password_field($obj, $attr, $opts);
    default:
      throw new Exception("unhandled form_field type `$type`");
  }
}

function form_errors($obj, $attr) {
  $errors = $obj->errors->on($attr);
  if(empty($errors))
    return '';
  $errors = implode(', ', $errors);
  return "<p class='error'>$errors</p>";
}

function form_row($type, $obj, $attr, $opts = array()) {

  $html = array();
  $html[] = form_label($obj, $attr, $opts);
  $html[] = form_field($type, $obj, $attr, $opts);

  if($errors = form_errors($obj, $attr, $opts))
    $html[] = tag('div', $errors, array('class' => 'errors'));

  if(isset($opts['hint']))
    $html[] = tag('div', $opts['hint'], array('class' => 'hint'));

  $css = array();
  if(isset($opts['required']) && $opts['required']) $css[] = 'required';
  if(isset($opts['class'])) $css[] = $opts['class'];
  if($errors) $css[] = 'invalid';
  $css[] = $type;
  $css[] = 'row';
  $css = implode(' ', array_unique($css));

  return tag('div', $html, array('class' => $css));

}

function text_field_row($obj, $attr, $opts = array()) {
  return form_row('text', $obj, $attr, $opts);
}

function checkbox_field_row($obj, $attr, $opts = array()) {
  return form_row('checkbox', $obj, $attr, $opts);
}

function password_field_row($obj, $attr, $opts = array()) {
  return form_row('password', $obj, $attr, $opts);
}

function submit_button_row($label = 'Submit', $opts = array()) {
  $opts['type'] = 'submit';
  $opts['value'] = $label;
  $submit = tag('input', null, $opts);
  return tag('div', $submit, array('class' => 'submit row'));
}





function form_field_title($obj, $attr) {
  return ucfirst($attr);
}

function form_field_id($obj, $attr) {
  return strtolower(get_class($obj) . "_$attr");
}

function camelize($lower_case_and_underscored_word) {
  $str = $lower_case_and_underscored_word;
  return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
}

function capitalize($word) {
  return ucfirst(strtolower($word));
}

function classify($word) {
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

function css_tag($asset, $opts = array()) {
  if($asset[0] == '/' or preg_match('#^https?://#', $asset, $matches))
    $url = $asset;
  else
    $url = "/stylesheets/$asset.css";
  $media = isset($opts['media']) ? $opts['media'] : 'screen';
  return "<link href='$url' media='$media' rel='stylesheet' type='text/css' />";
}

function js_tag($asset) {

  $url = $asset;
  if(!str_ends_with($url, '.js'))
    $url .= '.js';

  if($url[0] != '/' && !preg_match('#^https?://#', $asset, $matches))
    $url = "/javascripts/$url";

  return "<script src='$url' type='text/javascript'></script>";
}

function tag($name, $content = null, $attributes = array()) {

  $self_closing = in_array($name, array(
    'meta', 'img', 'link', 'script', 'br', 'hr',
  ));

  # build the attributes
  $attr = array();
  foreach($attributes as $key => $value)
    if(!is_null($value)) {
      $value = htmlspecialchars($value, ENT_QUOTES);
      $attr[] = "$key='$value'";
    }
  $attr = empty($attr) ? '' : ' ' . implode(' ', $attr);

  if(is_array($content))
    $content = implode('', $content); 

  return $self_closing ? "<$name$attr />" : "<$name$attr>$content</$name>";
}

function link_tag($label, $url, $opts = array()) {

  $opts['href'] = url($url);

  if(isset($opts['confirm']) && $opts['confirm']) {
    $msg = $opts['confirm'];
    if($msg === true)
      $msg = 'Are your sure?';
    else
      $msg = str_replace('\'', '\\\'', $msg);
    unset($opts['confirm']);
    $opts['onclick'] = "return confirm('$msg');";
  }

  return tag('a', $label, $opts);
}

/**
 * Builds a url string
 *
 *
 */
function url() {

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

function uuid() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    mt_rand( 0, 0x0fff ) | 0x4000,
    mt_rand( 0, 0x3fff ) | 0x8000,
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
}

function add_include_path($path) {
  set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

/**
 * Returns true if the argument is an associative array (hash).
 *
 * @param array $array the array to test
 * @return boolean 
 */
function is_assoc($array) {
  return is_array($array) && array_diff_key($array, array_keys(array_keys($array)));
}

function array_delete(&$array, $key) {
  if(isset($array[$key])) {
    $value = $array[$key];
    unset($array[$key]);
    return $value;
  }
  return null;
}

function str_begins_with($string, $search) {
  return strncmp($string, $search, strlen($search)) == 0;
}

function str_ends_with($string, $search) {
  return substr($string, strlen($string) - strlen($search)) == $search;
}

function debug($obj, $stop = true) {
  echo '<pre>';
  var_dump($obj);
  #print_r($obj);
  echo "</pre>\n";
  if($stop)
    exit();
}

function collect($array, $callback) {
  $results = array();
  foreach($array as $key => $value)
    $results[] = $callback($key, $value);
  return $results;
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

function h($str) {
  return htmlspecialchars($str);
}
