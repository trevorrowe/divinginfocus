<?php

namespace Pippa;

# TODO : add request logging (ala rails request logging)
# TODO : add 404 responses
class Controller {

  protected $request;

  protected $_render_or_redirect;

  protected $_status;

  protected $_layout = 'application';

  protected $_statuses = array(
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
  }

  public function run() {
    $actn = $this->request->params['action'] . '_action';
    $this->$actn($this->request->params, $this->request);
    $this->render_or_redirect();
  }

  public function status($status) {
    $this->_status = $status;
  }

  public function render($what) {
    $this->_render_or_redirect = array('render', $what);
  }

  # Different render modes:
  #
  #                        # STA  LYT TEMPLATE          CONTENTTYPE
  #   render('edit');      # 200, yes edit.:format.php  based on format
  #   render_text('text'); # 200, yes none              based on format
  #   render_json('text'); # 200, no  none              application/json?
  #
  # If the first arg to render is prefixed with a /
  # 
  #   forms/whatever/foo
  #     app/views/:controller/forms/whatever/foo.:format.php
  #
  #   /forms/whatever/foo
  #     app/views/forms/whatever/foo.:format.php
  #
  # Rendering w/options
  #
  #   status("HTTP/1.1 101 Switching Protocols");
  #   status("HTTP/1.1 404 Switching Protocols");
  #
  #   render('edit', array('status' => 200)));
  #   render('edit', array('layout' => 'popup')));
  #   render('edit', array('layout' => 'popup')));
  #
  #   layout('popup');
  #   render('calculator');
  #
  #   status(404);
  #   render('missing');
  #
  # Formats
  #
  # Given the following request params:
  #
  #   controller: widgets
  #   action: show
  #   id: 123
  #   format: xml
  #
  # If no call to render was made the defaults would be
  # 
  #   template: show.xml
  #   layout: application.xml
  #
  # Something must happen, render, redirect or send_file

  public function render_text($text) {
    echo $text;
    exit;
  }

  # Examples:
  #   redirect($params);
  #   redirect('/some/path');
  #   redirect('action');
  #   redirect('action', 123); # actn, id
  #   redirect('controller', 'action', 123); # cntl, actn, id
  public function redirect() {
    $argc = func_num_args();
    $args = func_get_args();
    switch(true) {
      # redirect($params_hash)
      case $argc == 1 && is_array($args[0]):
        $where = url($args[0]);
        break;
      # redirect('/some/path');
      case $argc == 1 && $args[0][0] == '/':
        $where = $args[0];
        break;
      # redirect('index');
      case $argc == 1:
        $where = array(
          'controller' => $this->request->params['controller'],
          'action' => $args[0],
        );
        break;
      # redirect('show', 123);
      case $argc == 2:
        $where = array(
          'controller' => $this->request->params['controller'],
          'action' => $args[0],
          'id' => $args[1],
        );
        break;
      # redirect('other_controller', 'show', 123);
      case $argc == 3:
        $where = array(
          'controller' => $args[0],
          'action' => $args[1],
          'id' => $args[2],
        );
        break;
      default:
        throw new Exception('Invalid redirect params: ' . print_r($args, true));
    }
    $this->_render_or_redirect = array('redirect', $where);
  }

  protected function render_or_redirect() {

    $status = $this->_status ? $this->_status : 200;

    header($this->_statuses[$status]);
    switch($this->_render_or_redirect[0]) {
      case 'render':
        #echo "render template: {$this->render_or_redirect[1]}";
        $template = $this->_render_or_redirect[1];
        break;
      case 'redirect':
        header ("Location: {$this->_render_or_redirect[1]}");
        return;
      default:
        #echo "default render: {$this->request->params['action']}"; 
        $template = $this->request->params['action'];
    }

    $cntl = $this->request->params['controller'];
    $actn = $this->request->params['action'];

    $tmpl = App::root . '/app/views/'. $cntl . '/' . $actn . '.html.php';
      
    if(!file_exists($tmpl))
      throw new \Exception("Missing template file $tmpl"); 

    $layout = App::root . '/app/views/layouts/application.html.php';

    if($layout) {
      ob_start();
      include($tmpl);
      $page = ob_get_clean();
      ob_end_flush();
      include($layout);
    } else {
      include($tmpl);
    }
  }

  public function __call($method, $args) {
    # 404 page, action not found
  }

  /****************************************************************************
   * Class methods
   ***************************************************************************/

  public static function class_name($controller) {
    $class_name = '';
    foreach(explode('/', $controller) as $part)
      $class_name .= ucfirst($part);
    return $class_name . 'Controller';
  }

  public static function controller_path($controller) {
    return App::root . "/app/controllers/{$controller}_controller.php";
  }

  public static function exists($controller) {
    return file_exists(self::controller_path($controller));
  }

}
