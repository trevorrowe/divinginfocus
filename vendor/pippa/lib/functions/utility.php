<?php

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

function as_array($value) {
  return is_array($value) ? $value : array($value);
}

function array_remove($array, $value) {
   return array_values(array_diff($array, array($value)));
}

function str_begins_with($string, $search) {
  return strncmp($string, $search, strlen($search)) == 0;
}

function str_ends_with($string, $search) {
  return substr($string, strlen($string) - strlen($search)) == $search;
}

function dump($obj, $stop = true) {
  echo '<pre>';
  #var_dump($obj);
  print_r($obj);
  echo "</pre>\n";
  if($stop)
    exit();
}

function d() {
  $msg = array();
  foreach(func_get_args() as $arg)
    $msg[] = '<pre>' . print_r($arg, true) . '</pre>';
  throw new Exception(implode("\n", $msg));
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
