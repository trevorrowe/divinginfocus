<?php

namespace Pippa;

class View {

  protected $_locals;

  protected $_default_controller;

  protected $_default_format;

  public function __construct($default_controller, $default_format) {
    $this->_locals = Locals::get();
    $this->_default_controller = $default_controller;
    $this->_default_format = $default_format;
  }

  public function __get($name) {
    return $this->_locals->$name;
  }

  public function __set($name, $value) {
    $this->_locals->$name = $value;
  }

  public function __call($method, $args) {
    return Helper::invoke($method, $args);
  }

  public function render($template) {
    echo $this->render_to_string($template);
  }

  public function render_to_string($template) {
    return $this->_include($template);
  }

  protected function _include($template) {

    $suffix = ".{$this->_default_format}.php";
    if($template[0] == '/')
      $template = ltrim("$template$suffix", '/');
    else
      $template = "{$this->_default_controller}/$template$suffix";

    \App::$log->write("Rendering view: $template");

    # TODO : decide if locals should be aliases like this or not
    foreach($this->_locals as $name => $value)
      $$name = $value;

    ob_start();
    include(\App::root . "/app/views/$template");
    $results = ob_get_clean();
    return $results;

  }
  
}
