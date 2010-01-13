<?php

namespace Pippa;

class View {

  protected $_locals = array();

  protected $_default_controller;

  protected $_default_format;

  public function __construct($default_controller, $default_format) {
    $this->_default_controller = $default_controller;
    $this->_default_format = $default_format;
  }

  public function __get($name) {
    return isset($this->_locals[$name]) ? $this->_locals[$name] : null;
  }

  public function __set($name, $value) {
    $this->_locals[$name] = $value;
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

    App::$log->write("Rendering view: $template");

    foreach($this->_locals as $name => $value)
      $$name = $value;

    ob_start();
    include(App::root . "/app/views/$template");
    $results = ob_get_clean();
    return $results;

  }
  
}
