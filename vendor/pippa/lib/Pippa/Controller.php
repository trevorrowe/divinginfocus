<?php

namespace Pippa;

class Controller extends LocalsContainer {

  static $layout = 'application';
  
  const MODE_RENDER_TMPL    = 1;
  const MODE_RENDER_TEXT    = 2;
  const MODE_RENDER_FILE    = 3;
  const MODE_RENDER_NOTHING = 4;
  const MODE_REDIRECT       = 5;

  private $_before_filters = array();
  private $_after_filters = array();
  private $_around_filters = array();

  protected $view;

  protected $_locals;

  protected $_mode;

  protected $_mode_data;

  protected $_status;

  protected $_layout;

  public static $content_types = array(
    'html' => 'text/html',
    'txt'  => 'text/plain',
    'csv'  => 'text/csv',
    'xml'  => 'text/xml',
    'js'   => 'text/javascript',
    'css'  => 'text/css',
    'json' => 'application/json',
  );

  public static $statuses = array(
    100 => "HTTP/1.1 100 Continue",
    101 => "HTTP/1.1 101 Switching Protocols",
    200 => "HTTP/1.1 200 OK",
    201 => "HTTP/1.1 201 Created",
    202 => "HTTP/1.1 202 Accepted",
    203 => "HTTP/1.1 203 Non-Authoritative Information",
    204 => "HTTP/1.1 204 No Content",
    205 => "HTTP/1.1 205 Reset Content",
    206 => "HTTP/1.1 206 Partial Content",
    300 => "HTTP/1.1 300 Multiple Choices",
    301 => "HTTP/1.1 301 Moved Permanently",
    302 => "HTTP/1.1 302 Found",
    303 => "HTTP/1.1 303 See Other",
    304 => "HTTP/1.1 304 Not Modified",
    305 => "HTTP/1.1 305 Use Proxy",
    307 => "HTTP/1.1 307 Temporary Redirect",
    400 => "HTTP/1.1 400 Bad Request",
    401 => "HTTP/1.1 401 Unauthorized",
    402 => "HTTP/1.1 402 Payment Required",
    403 => "HTTP/1.1 403 Forbidden",
    404 => "HTTP/1.1 404 Not Found",
    405 => "HTTP/1.1 405 Method Not Allowed",
    406 => "HTTP/1.1 406 Not Acceptable",
    407 => "HTTP/1.1 407 Proxy Authentication Required",
    408 => "HTTP/1.1 408 Request Time-out",
    409 => "HTTP/1.1 409 Conflict",
    410 => "HTTP/1.1 410 Gone",
    411 => "HTTP/1.1 411 Length Required",
    412 => "HTTP/1.1 412 Precondition Failed",
    413 => "HTTP/1.1 413 Request Entity Too Large",
    414 => "HTTP/1.1 414 Request-URI Too Large",
    415 => "HTTP/1.1 415 Unsupported Media Type",
    416 => "HTTP/1.1 416 Requested range not satisfiable",
    417 => "HTTP/1.1 417 Expectation Failed",
    500 => "HTTP/1.1 500 Internal Server Error",
    501 => "HTTP/1.1 501 Not Implemented",
    502 => "HTTP/1.1 502 Bad Gateway",
    503 => "HTTP/1.1 503 Service Unavailable",
    504 => "HTTP/1.1 504 Gateway Time-out"
  );

  public function __construct($request) {

    parent::__construct();

    $this->request = $request;
    $this->params = $request->params;

    $cntl = $request->params['controller'];

    $this->view = new View($cntl, $request->format);

  }

  public function init() {}

  ##
  ## filter chain methods
  ##

  public function before_filter($filter, $opts = array()) {
    $this->append_before_filter($filter, $opts);
  }

  public function append_before_filter($filter, $opts = array()) {
    $this->_before_filters[] = array($filter, $opts);
  }

  public function prepend_before_filter($filter, $opts = array()) {
    array_unshift($this->_before_filters, array($filter, $opts));
  }

  public function skip_before_filter($filter_name) {
    foreach($this->_before_filters as $i => $filter) {
      if($filter[0] == $filter_name) {
        unset($this->_before_filters[$i]);
        return;
      }
    }
    $msg = "skip_before_filter called on a non-existant filter: $filter_name";
    throw new Exception($msg);
  }

  private function _run_before_filters() {
    foreach($this->_before_filters as $bf) {

      list($filter, $opts) = $bf;
      $filter_method = "{$filter}_filter";

      # run filter 'if' ...
      if(isset($opts['if']))
        foreach(as_array($opts['if']) as $if)
          if(!$this->$if())
            continue(2);

      # run filter 'unless' ...
      if(isset($opts['unless']))
        foreach(as_array($opts['unless']) as $unless)
          if($this->$unless())
            continue(2);

      # run filter 'only' on these actions ...
      if(isset($opts['only']))
        if(!in_array($this->params->action, as_array($opts['only'])))
          continue(1);

      # run filter on all actions 'except' ...
      if(isset($opts['except']))
        if(in_array($this->params->action, as_array($opts['except'])))
          continue(1);

      # the guantlet having been run, we can now call the filter method
      $this->$filter_method($this->params, $this->request);

      # halt the filter chain if $filter_method called render or redirect
      if($this->_render_or_redirect_called())
        return;
    }
  }

  public function after_filter($filter, $opts = array()) {
    $this->append_after_filter($filter, $opts);
  }

  public function append_after_filter($filter, $opts = array()) {
    $this->_after_filters[] = array($filter, $opts);
  }

  public function prepend_after_filter($filter, $opts = array()) {
    array_unshift($this->_after_filters, array($filter, $opts));
  }

  # TODO : write after filter logic
  private function _run_after_filters() { }

  ##
  ## magic methods that pass along to the helpers
  ##

  public function __call($method, $args) {
    if(str_ends_with($method, '_action'))
      throw new Exceptions\UndefinedAction($this->request);
    return parent::__call($method, $args);
  }

  ##
  ## running the request
  ##

  # called by the Router during dispatch
  public function run() {

    $this->init();

    $this->_run_before_filters();

    if($this->_render_or_redirect_called()) {
      # before filters can call render or redirect, effectively halting
      # the filter chain and by-passing the standard action
      # TODO : log that the filter chain was halted
      $this->_render_or_redirect();
    } else {
      $action_method = $this->request->params['action'] . '_action';
      $this->$action_method($this->request->params, $this->request);
      $this->_run_after_filters();
      $this->_render_or_redirect();
    }

  }

  private function _render_or_redirect_called() {
    return $this->_mode != null;
  }

  protected function layout($which) {
    $this->_layout = $which;
  }

  protected function status($status) {
    $this->_status = $status;
  }

  protected function render($what, $opts = array()) {
    $this->_check_render_redirect();
    if($what == false) {
      $this->_mode = self::MODE_RENDER_NOTHING;  
      $this->_mode_data = null;
    } else {
      $this->_parse_std_options($opts);
      $this->_mode = self::MODE_RENDER_TMPL;
      $this->_mode_data = $what;
    }
  }

  protected function render_text($text, $opts = array()) {
    $this->_check_render_redirect();
    $this->_parse_std_options($opts);
    $this->_mode = self::MODE_RENDER_TEXT;
    $this->_mode_data = $text;
  }

  protected function render_file($path, $opts = array()) {
    $this->_check_render_redirect();
    $this->_parse_std_options($opts);
    $this->_mode = self::MODE_RENDER_FILE;
    $this->_mode_data = $path;
  }

  protected function render_error_page($status) {
    $this->status($status);
    $this->layout(false);
    if(in_array((int) $status, \App::$cache->error_pages)) {
      $this->render_file(\App::root . "/public/$status.html");
    } else {
      $parts = explode(' ', self::$statuses[$status]);
      $this->render_text("{$parts[1]} {$parts[2]}");
    }
  }

  protected function redirect() {

    $this->_check_render_redirect();

    $args = func_get_args();
    $argc = func_num_args();

    if($argc > 1 && is_array($args[$argc - 1])) {
      $this->_parse_std_options(array_pop($args));
      $argc -= 1;
    }

    $this->_mode = self::MODE_REDIRECT;
    $this->_mode_data = call_user_func_array('url', $args);

  }

  private function _render_or_redirect() {
    
    # if the user called neither render or redirect then the default 
    # is rendering the template by the same name as the current action
    if(is_null($this->_mode)) {
      $this->_mode = self::MODE_RENDER_TMPL;
      $this->_mode_data = $this->request->params['action'];
    }

    if($this->_mode == self::MODE_REDIRECT) {

      ## redirect 

      header(self::$statuses[$this->_status ? $this->_status : 302]);
      header ("Location: {$this->_mode_data}");
      \App::$log->write("Redirected to {$this->_mode_data}");

    } else {

      ## render

      switch($this->_mode) {
        case self::MODE_RENDER_TMPL:
          $this->_render_template();
          break;
        case self::MODE_RENDER_TEXT:
          $this->_render_text($this->_mode_data);
          break;
        case self::MODE_RENDER_FILE:
          $this->_render_text(file_get_contents($this->_mode_data));
          break;
        case self::MODE_RENDER_NOTHING:
          break;
        default:
          throw new Exception("Unknown render mode: {$this->_mode}");
      }
    }
  }

  private function _render_template() {
    $tmpl = $this->_mode_data;
    if(is_null($tmpl))
      $tmpl = $this->request->params['action'];
    $this->_render_text($this->_include_with_locals($tmpl));
  }

  private function _render_text($page) {
    
    $default = static::$layout;
    $layout = $this->_layout;

    switch(true) {
      case $layout === NULL:
        $layout = $this->request->format == 'html' ? $default : FALSE;
        break;
      case $layout === FALSE:
        $layout = FALSE;
        break;
      case $layout === TRUE:
        $layout = $default;
        break;
      default:
        $layout = $this->_layout;
    }

    # set the http status header
    header(self::$statuses[$this->_status ? $this->_status : 200]);

    ## set the content type header

    $format = $this->request->format;
    if(!isset(self::$content_types[$format]))
      throw new Exception("Unknown content type for format: $format");
    $content_type = self::$content_types[$format];
    header("Content-type: $content_type");

    ## display the page

    if($layout)
      echo $this->_include_with_locals("/layouts/$layout", $page);
    else
      echo $page;

  }

  private function _include_with_locals($file, $content = NULL) {
    $this->view->_content = $content;
    return $this->view->render_to_string($file);
  }

  private function _check_render_redirect() {
    if($this->_render_or_redirect_called()) {
      $msg = "render or redirect has already been called for this action";
      throw new Exception($msg);
    }
  }

  private function _parse_std_options($options) {
    foreach(array('layout', 'status') as $option_name) 
      if(isset($options[$option_name]))
        $this->$option_name($options[$option_name]);
  }

  public static function class_name($controller) {
    $parts = explode('/', $controller);
    $last_part = count($parts) - 1;
    foreach($parts as $i => $part) {
      if($i == $last_part)
        $parts[$i] = camelize("{$part}_controller");
      else
        $parts[$i] = camelize($part);
    }
    return '\\' . implode('\\', $parts);
  }

}
