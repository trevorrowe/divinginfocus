<?php

namespace Pippa;

# TODO : add request logging (ala rails request logging)
# TODO : add 404 responses

class Controller {
  
  const MODE_RENDER_TMPL = 1;
  const MODE_RENDER_TEXT = 2;
  const MODE_REDIRECT = 3;

  protected $request;

  protected $params;

  protected $locals = array();

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

  public function __construct(Request $request) {
    $this->request = $request;
    $this->params = &$request->params;
    $this->locals['request'] = &$this->request;
    $this->locals['params'] = &$this->params;
  }

  # called by the Router during dispatch
  public function run() {
    $action_method = $this->params['action'] . '_action';
    $this->$action_method($this->params, $this->request);
    $this->_render_or_redirect();
  }

  public function layout($which) {
    $this->_layout = $which;
  }

  public function status($status) {
    $this->_status = $status;
  }

  public function render($what, $opts = array()) {
    $this->_check_render_redirect();
    $this->_parse_std_options($opts);
    $this->_mode = self::MODE_RENDER_TMPL;
    $this->_mode_data = $what;
  }

  public function render_text($text, $opts = array()) {
    $this->_check_render_redirect();
    $this->_parse_std_options($opts);
    $this->_mode = self::MODE_RENDER_TEXT;
    $this->_mode_data = $text;
  }

  public function redirect() {

    $this->_check_render_redirect();

    $args = func_get_args();
    $argc = func_num_args();

    if($argc > 1 && is_array($args[$argc - 1])) {
      $this->_parse_std_options(array_pop($args));
      $argc -= 1;
    }

    switch(true) {

      case $argc == 1 && is_array($args[0]): # redirect($params_hash)
      case $argc == 1 && $args[0][0] == '/': # redirect('/some/url/path')
        $where = $args[0];
        break;

      case $argc == 1: # redirect(:action)
        $where = array(
          'controller' => $this->params['controller'],
          'action' => $args[0],
        );
        break;

      case $argc == 2: # redirect(:action, :id)
        $where = array(
          'controller' => $this->params['controller'],
          'action' => $args[0],
          'id' => $args[1],
        );
        break;

      case $argc == 3: # redirect(:controller, :action, :id);
        $where = array(
          'controller' => $args[0],
          'action' => $args[1],
          'id' => $args[2],
        );
        break;

      default:
        throw new Exception('Invalid redirect params: ' . print_r($args, true));

    }
    $this->_mode = self::MODE_REDIRECT;
    $this->_mode_data = url($where);
  }

  protected function _render_or_redirect() {
    
    # if the user called neither render or redirect then the default 
    # is rendering the template by the same name as the current action
    if(is_null($this->_mode)) {
      $this->_mode = self::MODE_RENDER_TMPL;
      $this->_mode_data = $this->params['action'];
    }
    
    if($this->_mode == self::MODE_REDIRECT)
      $this->_redirect();
    else
      $this->_render();

  }

  protected function _redirect() {
    header(self::$statuses[$this->_status ? $this->_status : 302]);
    header ("Location: {$this->_mode_data}");
    App::$log->write("Redirected to {$this->_mode_data}");
  }

  protected function _render() {
    switch($this->_mode) {
      case self::MODE_RENDER_TMPL:
        $this->_render_tmpl();
        break;
      case self::MODE_RENDER_TEXT:
        $this->_render_text($this->_mode_data);
        break;
      default:
        throw new Exception("Unknown render mode: {$this->_mode}");
    }
  }

  protected function _render_tmpl() {

    $cntl = $this->params['controller'];
    $actn = $this->params['action'];
    $suffix = $this->_tmpl_suffix();

    $tmpl = $this->_mode_data;

    switch(true) {
      case is_null($tmpl):
        $tmpl = App::root . "/app/views/$cntl/$actn$suffix";
        break;
      case $tmpl[0] == '/':
        $tmpl = App::root . "/app/views/$tmpl$suffix";
        break;
      default:
        $tmpl = App::root . "/app/views/$cntl/$tmpl$suffix";
    }

    if(!file_exists($tmpl))
      throw new \Exception("Missing template file $tmpl"); 

    $this->_render_text($this->_include_with_locals($tmpl));
  }

  protected function _render_text($page) {

    $default = 'application';
    $layout = $this->_layout;

    switch(true) {
      case $layout === NULL:
        $layout = $this->_format() == 'html' ? $default : FALSE;
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

    # set the content type header
    header('Content-type: ' . $this->_content_type());

    # display the page
    if($layout) {
      $file = App::root . "/app/views/layouts/$layout" . $this->_tmpl_suffix();
      echo $this->_include_with_locals($file, $page);
    } else {
      echo $page;
    }

  }

  protected function _content_type() {
    $format = $this->_format();
    if(!isset(self::$content_types[$format]))
      throw new Exception("Unknown content type for format: $format");
    return self::$content_types[$format];
  }

  protected function _format() {
    return isset($this->params['format']) ? $this->params['format'] : 'html';
  }

  protected function _tmpl_suffix() {
    return '.' . $this->_format() . '.php';
  }

  protected function _include_with_locals($file, $content = NULL) {

    foreach($this->locals as $name => &$value)
      $$name = &$value;

    ob_start();
    include($file);
    $results = ob_get_clean();
    ob_end_flush();

    return $results;
  }

  protected function _check_render_redirect() {
    if($this->_mode != NULL) {
      $msg = "render or redirect has already been called for this action";
      throw new Exception($msg);
    }
  }

  protected function _parse_std_options($options) {
    foreach(array('layout', 'status') as $option_name) 
      if(isset($options[$option_name]))
        $this->$option_name($options[$option_name]);
  }

  public function __call($method, $args) {
    # 404 page, action not found
  }

  # TODO : this function needs to support $controller values like:
  #
  #   admin/directory_pages_controller => Admin_DirectoryPagesController
  #
  public static function class_name($controller) {
    $class_name = '';
    foreach(explode('/', $controller) as $part)
      $class_name .= ucfirst($part);
    return $class_name . 'Controller';
  }

  public static function controller_path($controller) {
    return App::root . "/app/controllers/{$controller}_controller.php";
  }

}
