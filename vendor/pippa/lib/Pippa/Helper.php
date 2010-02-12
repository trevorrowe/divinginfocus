<?php

namespace Pippa;

class Helper {

  private static $helper_classes = array();

  private static $helpers = array();

  public function __get($key) {
    return Locals::get()->$key;
  }

  public function __set($key, $value) {
    Locals::get()->$key = $value;
  }

  public function __call($method, $args) {
    return self::invoke($method, $args);
  }

  public function get_opt($opts, $key, $default) {
    return array_key_exists($key, $opts) ? $opts[$key] : $default;
  }

  public function append_class_name(&$opts, $class) {
    if(isset($opts['class'])) {
      if(!preg_match("/(^|\s+)$class(\s+|$)/", $opts['class']))
        $opts['class'] = "{$opts['class']} $class";
    } else {
      $opts['class'] = $class;
    }
  }

  public static function register($helper_class) {
    array_unshift(self::$helper_classes, $helper_class);
  }
  
  public static function invoke($method, $args) {
    foreach(self::$helper_classes as $helper_class) {
      $helper = self::get($helper_class);
      if(method_exists($helper, $method))
        return call_user_func_array(array($helper, $method), $args);
    }
    throw new Exceptions\UndefinedHelper($method);
  }

  public static function get($helper_class) {
    if(!isset(self::$helpers[$helper_class]))
      self::$helpers[$helper_class] = new $helper_class();
    return self::$helpers[$helper_class];
  }

}
