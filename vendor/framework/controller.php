<?php

namespace Framework;

class Controller {

  protected $request;

  protected $render_or_redirect;

  protected $status;

  protected $statuses = array (
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
    $this->$actn($this->request->params);
    $this->render_or_redirect();
  }

  public function status($status) {
    $this->status = $status;
  }

  public function render($what) {
    $this->render_or_redirect = array('render', $what);
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
    $this->render_or_redirect = array('redirect', $where);
  }

  protected function render_or_redirect() {
    $status = $this->status ? $this->status : 200;
    header($this->statuses[$status]);
    switch($this->render_or_redirect[0]) {
      case 'render':
        #echo "render template: {$this->render_or_redirect[1]}";
        $template = $this->render_or_redirect[1];
        break;
      case 'redirect':
        header ("Location: {$this->render_or_redirect[1]}");
        return;
      default:
        #echo "default render: {$this->request->params['action']}"; 
        $template = $this->request->params['action'];
    }

    require_once(App::root . '/vendor/phml/phml_orig.php');

    $cntl = $this->request->params['controller'];
    $actn = $this->request->params['action'];

    $cache = false;
    $cache_dir = App::root . '/tmp/phml/' . $cntl;
    if(!file_exists($cache_dir)) mkdir($cache_dir, 0777, true);

    $haml_parser = new \HamlParser($cache_dir, $cache);
    $haml_parser->append(array(
      'request' => $this->request,
      'params' => $this->request->params,
      'foo' => 'bar',
    ));

    $tmpl = App::root . '/app/views/'. $cntl . '/' . $actn . '.html.phml';
    $haml_parser->display($tmpl);

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
