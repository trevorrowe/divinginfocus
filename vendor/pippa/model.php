<?php

namespace Pippa;

# Important features
# 
# * dirty tracking
# * whitelist
# * exceptional finds and saves
# * callbacks
# * timestamps
# * validations + errors
# * serializations (to hash/json)
# * identity map
#
# Finders:
# 
# return a collection
# * object identity map, never allow more than one of each object
# * associations (bulk hydration) 
# 

class Model {

  ### class configuration

  protected static $columns;

  protected static $whitelist;



  public $errors;

  protected $_attributes = array();

  protected $_dirty = array();

  protected static $id_attr = 'id';

  protected static $attributes = array();

  protected $new_record = true;

  protected $attributes = array();

  protected $attributes_orig = array();

  public function __construct($attributes = array()) {
    $this->attributes($attributes);
  }

  public function __set($attr) {
  }

  public function __get($attr) {
  }

  public function __isset() {
  }

  public function __unset() {
  }

  public function __call($method, $arguments) {
  }

  public function dirty() {
    return true;
  }

  public function attributes($attributes = null) {
    
    # user want the read, not set attributes
    if(is_null($attributes))
      return $this->attributes;

    foreach($attributes as $attr_name => $attr_value) {
      if(isset(self::$attributes[$attr_name])) {
      } else {
        throw new Exception("");
      }
    }

    if($set) {
      foreach($set as $attr => $value) {
        $this->data[$attr] = $value;
      }
    } else {
      return $this->attributes;
    }
  }

  public function to_param() { }

  ### finders

  public static function first() { }

  # Klass::get(1)
  # Klass::get(1, 2, 3)
  # Klass::get(array(1, 2, 3))
  public static function get() { 
  }

}
