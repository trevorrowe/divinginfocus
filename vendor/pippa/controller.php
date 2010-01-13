<?php

namespace Pippa;

class Controller {
  
  const MODE_RENDER_TMPL = 1;
  const MODE_RENDER_TEXT = 2;
  const MODE_REDIRECT = 3;

  protected $view;

  protected $request;

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

    $cntl = $request->params['controller'];
    $this->view = new View($cntl, $request->format);

    $this->request = $request;

    $this->view->request = $this->request;
    $this->view->params = $this->request->params;

  }

  public function __set($name, $value) {
    $this->view->$name = $value;
  }

  public function __get($name) {
    return $this->view->$name;
  }

  public function __call($method, $args) {
    if(str_ends_with($method, 'action')) {
      throw new UndefinedActionException($this->request);
    } else {
      $class = get_called_class();
      throw new Exception("undefined method $class::$method");
    }
  }

  # called by the Router during dispatch
  public function run() {
    $action_method = $this->request->params['action'] . '_action';
    $this->$action_method($this->request->params, $this->request);
    $this->_render_or_redirect();
  }

  public function add_helper($name) {
    require(App::root . "/app/helpers/{$name}_helper.php");
  }

  protected function layout($which) {
    $this->_layout = $which;
  }

  protected function status($status) {
    $this->_status = $status;
  }

  protected function render($what, $opts = array()) {
    $this->_check_render_redirect();
    $this->_parse_std_options($opts);
    $this->_mode = self::MODE_RENDER_TMPL;
    $this->_mode_data = $what;
  }

  protected function render_text($text, $opts = array()) {
    $this->_check_render_redirect();
    $this->_parse_std_options($opts);
    $this->_mode = self::MODE_RENDER_TEXT;
    $this->_mode_data = $text;
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
      App::$log->write("Redirected to {$this->_mode_data}");

    } else {

      ## render

      switch($this->_mode) {
        case self::MODE_RENDER_TMPL:
          $this->_render_template();
          break;
        case self::MODE_RENDER_TEXT:
          $this->_render_text($this->_mode_data);
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
    if($this->_mode != NULL) {
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
    $parts = array();
    foreach(explode('/', $controller) as $part)
      $parts[] = camelize($part);
    $c = implode('_', $parts) . 'Controller';
    return implode('_', $parts) . 'Controller';
  }

  public static function controller_path($class_name) {
    $parts = array();
    foreach(explode('_', $class_name) as $part)
      $parts[] = underscore($part);
    $path = implode('/', $parts);
    return App::root . "/app/controllers/{$path}.php";
  }

}
