<?php

namespace Pippa;

class App {

  const root = APP_ROOT;

  const env = APP_ENV;

  public static $cfg;

  public static $log;

  public static $routes = array();

  public static $controllers = array();

  public static function autoload($class) {
    if(substr($class, 0, 6) == 'Pippa\\') {
      # pippa framework classes
      $dir = self::root . '/vendor/pippa/src/classes/';
      require($dir . strtolower(str_replace('\\', '/', $class)) . '.php');
    } else if(substr($class, strlen($class) - 10) == 'Controller') {
      # controllers
      require Controller::controller_path($class);
    }
  }

  public static function run() {
    self::boot();
    Flash::init();
    ob_start();
    Router::dispatch(Request::get_http_request());
    Flash::clean();
  }

  public static function boot() {

    $log_path = App::root . '/log/' . App::env;

    ini_set('error_log', $log_path);

    spl_autoload_register("\Pippa\App::autoload");

    self::$log = new Logger($log_path);

    require(self::root . '/vendor/pippa/PippaFunctions.php');

    foreach(glob(self::root . '/config/initializers/*.php') as $file)
      require($file);

    require(self::root . '/config/routes.php');

    ## add standard include paths

    set_include_path(App::root . '/app/models');
    add_include_path(App::root . '/lib');
    spl_autoload_register(function($class) {
      spl_autoload($class, '.php');
    });

    # determine the complete list of routeable controllers 

    $controller_dir = self::root . '/app/controllers/';
    $start = strlen($controller_dir);
    $ite = new \RecursiveDirectoryIterator($controller_dir);
    foreach(new \RecursiveIteratorIterator($ite) as $filename => $cur) {
      if(substr_compare($filename, '_controller.php', -15) == 0) {
        $stop = strlen($filename) - 15 - $start;
        array_push(self::$controllers, substr($filename, $start, $stop));
      }
    }

  }
}

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

class Exception extends \Exception {}

# produces a 404 page in deployed environments
class NoMatchingRouteException extends Exception {

  public function __construct($request) {
    $msg = "No route matches '{$request->uri}' with method {$request->method}";
    parent::__construct($msg);
  }

}

# produces a 404 page in deployed environments
class UndefinedActionException extends Exception {

  public function __construct($request) {

    $cntl = Controller::class_name($request->params['controller']);
    $actn = $request->params['action'];

    $reflect = new \ReflectionClass($cntl);
    $methods = $reflect->getMethods(
      \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);

    $actions = array();
    foreach($methods as $method)
      if(str_ends_with($method->name, '_action')) 
        $actions[] = substr($method->name, 0, -7);
    $actions = implode(', ', $actions);

    $msg = "{$actn}_action not defined in $cntl, valid actions: $actions";
    parent::__construct($msg);
  }

}

class Flash {

  const cookie_name = '_pippa_flash';

  public static $data = array();

  protected static $to_expire = array();

  public static function set($key, $payload, $now = false) {
    self::$data[$key] = $payload;
    if($now)
      self::$to_expire[] = $key;
  }

  public static function get($key) {
    return isset(self::$data[$key]) ? self::$data[$key] : null;
  }

  public static function init() {
    if(isset($_COOKIE[self::cookie_name]))
      self::$data = unserialize($_COOKIE[self::cookie_name]);
    self::$to_expire = array_keys(self::$data);
  }

  public static function clean() {
    foreach(self::$to_expire as $key)
      unset(self::$data[$key]);
    $data = serialize(self::$data);
    $domain = '.divinginfocus.lappy';
    setcookie(self::cookie_name, $data, 0, '/', $domain, false, true);
  }

}

# camelize
# capitalize
# classify
# dasherize
# foreign_key
# humanize
# ordinalize
# pluralize
# singularize
# tableize
# titleize
# underscore

class Inflect {

  public static $singular = array(
    array('/(quiz)zes$/i', '\1'), 
    array('/(matr)ices$/i', '\1ix'), 
    array('/(vert|ind)ices$/i', '\1ex'), 
    array('/^(ox)en/i', '\1'), 
    array('/(alias|status)es$/i', '\1'), 
    array('/([octop|vir])i$/i', '\1us'), 
    array('/(cris|ax|test)es$/i', '\1is'), 
    array('/(shoe)s$/i', '\1'), 
    array('/(o)es$/i', '\1'), 
    array('/(bus)es$/i', '\1'), 
    array('/([m|l])ice$/i', '\1ouse'), 
    array('/(x|ch|ss|sh)es$/i', '\1'), 
    array('/(m)ovies$/i', '\1ovie'), 
    array('/(s)eries$/i', '\1eries'), 
    array('/([^aeiouy]|qu)ies$/i', '\1y'), 
    array('/([lr])ves$/i', '\1f'), 
    array('/(tive)s$/i', '\1'), 
    array('/(hive)s$/i', '\1'), 
    array('/([^f])ves$/i', '\1fe'), 
    array('/(^analy)ses$/i', '\1sis'), 
    array('/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i', '\1\2sis'), 
    array('/([ti])a$/i', '\1um'), 
    array('/(n)ews$/i', '\1ews'), 
    array('/s$/i', ''),
  );

  public static $plural = array(
    array('/(quiz)$/i', '\1zes'),
    array('/^(ox)$/i', '\1en'),
    array('/([m|l])ouse$/i', '\1ice'),
    array('/(matr|vert|ind)ix|ex$/i', '\1ices'),
    array('/(x|ch|ss|sh)$/i', '\1es'),
    array('/([^aeiouy]|qu)ies$/i', '\1y'),
    array('/([^aeiouy]|qu)y$/i', '\1ies'),
    array('/(hive)$/i', '\1s'),
    array('/(?:([^f])fe|([lr])f)$/i', '\1\2ves'),
    array('/sis$/i', 'ses'),
    array('/([ti])um$/i', '\1a'),
    array('/(buffal|tomat)o$/i', '\1oes'),
    array('/(bu)s$/i', '\1ses'),
    array('/(alias|status)$/i', '\1es'),
    array('/(octop|vir)us$/i', '\1i'),
    array('/(ax|test)is$/i', '\1es'),
    array('/s$/i', 's'),
    array('/$/', 's'),
  );

  public static $uncountable = array(
    'equipment', 'fish', 'information', 'money', 'rice', 'species', 
    'series', 'sheep',
  );

  public static $human = array();

  public static $cache = array(
    'singular' => array(),
    'plural' => array(),
  );

  public static function singular($regex, $replace) {
    array_unshift(self::$plural, array($regex, $replace));
  }

  public static function plural($regex, $replace) {
    array_unshift(self::$singular, array($regex, $replace));
  }

  public static function irregular($singluar, $plural) {
    self::plural('/('.preg_quote(substr($singular,0,1)).')'.preg_quote(substr($singular,1)).'$/i', '\1'.preg_quote(substr($plural,1)));
    self::singular('/('.preg_quote(substr($plural,0,1)).')'.preg_quote(substr($plural,1)).'$/i', '\1'.preg_quote(substr($singular,1)));
  }

  public static function uncountable($word) {
    self::$uncountable[] = $word;
  }

  public static function human($regex, $replace) {
    array_unshift(self::$human, array($regex, $replace));
  }

}

class Logger {

  public $stream;

  protected $indent = false;

  public function __construct($path) {
    $url = "file://$path";
    if(!$this->stream = @fopen($url, 'a', false))
      throw new Exception('Unable to write to log: ' . $this->path);
  }

  public function log($msg) {
    $this->write($msg);
  }

  public function write($msg) {
    $padding = $this->indent ? '  ' : '';
    # TODO : rework this, not a fan of the @ infront of fwrite
    if(false === @fwrite($this->stream, $padding . $msg . "\n"))
      throw new Exception('Unable to write to log: ' . $this->path);
  }
  
  public function request($request) {
    $cntl = $request->params['controller'];
    $actn = $request->params['action'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = strftime('%Y-%m-%d %T');
    $method = $request->method;
    $msg = "\nProcessing $cntl#$actn (for $ip at $time) [$method]";
    $this->write($msg);
    $this->indent = true;
  }

  public function params($params) {
    $param_string = 'array(';
    $param_string .= implode(', ', collect($params, function($k,$v) {
      $v = str_replace('\\', '\\\\', $v);
      $v = str_replace('\'', '\\\'', $v);
      return "'$k' => '$v'";
    }));
    $param_string .= ')';
    $this->write('Parameters: ' . $param_string);
  }

  public function timing($seconds) {
    $time = self::format_seconds($seconds);
    $status = 200;
    $url = Request::get_http_request()->url;
    $memory = format_bytes(memory_get_peak_usage(true));
    $msg = "Completed in $time using $memory | $status [$url]";
    $this->indent = false;
    $this->write($msg);
  }

  protected static function format_seconds($seconds) {
    $milli = round($seconds * 10000.0) / 10.0;
    switch(true) {
      case $milli < 1000: 
        return sprintf("%.1fms", $milli); 
      case $milli < (1000 * 60): 
        return sprintf("%.2f seconds", $milli / 1000); 
      default:
        $mins = floor(($milli / 1000) / 60);
        $seconds = ($milli - $mins * 1000 * 60) / 1000;
        return sprintf("%d mins %.2f seconds", $mins, $seconds);
    }
  }

}

class Request {

  public $url;

  public $uri;

  public $protocol;

  public $host;

  public $port;

  public $method;

  public $time;

  public $format;

  public $params;

  public $routeable_path;

  public function __construct($uri, $opts = array()) {

    $defaults = array(
      'uri'      => $uri,
      'protocol' => 'http',
      'host'     => 'localhost',
      'port'     => 80,
      'method'   => 'GET',
      'time'     => time(),
      'params'   => array(),
    );

    foreach($defaults as $k => $default) {
      if(isset($opts[$k]))
        $this->$k = $opts[$k];
      else
        $this->$k = $default;
    }

    $parts = explode('?', $uri);
    $parts = explode('.', $parts[0]);
    $this->routeable_path = trim($parts[0], '/');

    if(isset($parts[1])) {
      $this->format = $parts[1];
      $this->params['format'] = $parts[1];
    } else {
      $this->format = 'html';
    }

    $this->uri = $uri;
    $this->url = $this->protocol . "://{$this->host}$uri";

  }

  public function dispatch($route_params) {
    $this->params = array_merge($this->params, $route_params);
  }

  public static function &get_http_request() {
    static $http_request;
    if(!$http_request) {
      $http_request = new Request($_SERVER['REQUEST_URI'], array(
        'protocol' => 'http',
        'host'     => $_SERVER['HTTP_HOST'],
        'port'     => $_SERVER['SERVER_PORT'],
        'method'   => $_SERVER['REQUEST_METHOD'],
        'time'     => $_SERVER['REQUEST_TIME'],
        'params'   => $_REQUEST,
      ));
    }
    return $http_request;
  }

}

class Route {

  protected $name;

  protected $pattern;

  protected $regex;

  protected $req = array();

  protected $required_params;

  protected $match_indexes = array();

  public function __construct($name, $pattern, $params = array()) {

    $this->name = $name;
    $this->pattern = trim($pattern, '/');
    $this->req = $params;

    foreach(array('controller', 'action') as $req) {
      # controller and action may be provided in only one place
      if(isset($this->req[$req]) && preg_match("/:$req/", $this->pattern)) {
        $err = "$req may be in the route pattern or requirements, but no both";
        throw new Exception($err);
      }
      # controller and action both default to index when not provided
      if(!isset($this->req[$req]) && !preg_match("/:$req/", $this->pattern))
        $this->req[$req] = 'index';
    }
  }

  public function matches_request($request) {

    if(!$this->matches_method($request)) 
      return false;

    if(!$this->matches_format($request)) 
      return false;

    $params = $this->matches_pattern($request);
    if(!$params) 
      return false;

    if(!in_array($params['controller'], App::$controllers))
      return false;

    foreach($params as $k => $v)
      $request->params[$k] = $v;

    return true;
  }

  protected function matches_method($request) {
    return isset($this->req['method']) ? 
      $this->req['method'] == $request->method : 
      true;
  }

  protected function matches_format($request) {
    return isset($this->req['format']) ? 
      $this->req['format'] == $request->format : 
      true;
  }

  protected function matches_pattern($request) {

    $this->compile();
    if(!preg_match($this->regex, $request->routeable_path, $matches))
      return false;

    $params = array();
    foreach($this->match_indexes as $i => $name)
      $params[$name] = $matches[$i];

    foreach(array('controller', 'action', 'format') as $r)
      if(isset($this->req[$r])) $params[$r] = $this->req[$r];

    return $params;
  }

  protected function compile() {

    $match_index = 0;

    # build a regex based on the route pattern and requirements
    $regex = array();
    foreach(explode('/', $this->pattern) as $segment) {

      # the empty route pattern is the root path "/"
      if($segment == '') continue;

      # a static route segment like archive in the following example:
      # /:controller/archive/:year/:month
      if($segment[0] != ':') {
        array_push($regex, $segment);
        $match_index += $this->count_captures($segment);
        continue;
      }

      # strip the leading : from the segment name
      $segment = substr($segment, 1);

      # keep track of where in the array of regex matches this segment
      # will store its value.
      $this->match_indexes[++$match_index] = $segment;

      if(isset($this->req[$segment])) {
        $requirement = $this->req[$segment];
        $requirement = preg_replace('/\./', '[^/]', $requirement);
        array_push($regex, "($requirement)");
        $match_index += $this->count_captures($requirement);
      }
      else if($segment == 'controller')
        array_push($regex, '(\w[/\w]*)');
      else if($segment == 'action')
        array_push($regex, '(\w+)');
      else
        array_push($regex, '([^/]+)');
    }

    $regex = implode('/', $regex);
    $this->regex = "#^$regex$#";

  }

  protected function count_captures($str) {
    return preg_match_all('/\(/', $str, $discard);
  }

  public function matches_params($params) {

    $required_params = $this->required_params();
    $given_params = array_keys($params);
    $diff = array_diff($required_params, $given_params);
    if(!empty($diff))
      return false;
    
    foreach($this->req as $req => $regex)
      if(!preg_match("#^$regex$#", $params[$req])) 
        return false;

    return true;
  }

  public function build_url($params) {

    $current_request = Request::get_http_request();

    $path = array();

    foreach(explode('/', $this->pattern) as $segment) {
      if($segment == '') continue;

      # TODO : handle the '' path route
      if($segment[0] == ':') {
        $segment = substr($segment, 1);
if(!isset($params[$segment]) && !isset($current_reqeust->params[$segment])) {
  debug($segment, false);
  debug($current_request, false);
  debug($params, false);
  debug($this, false);
  throw new Exception('oops');
}
        $value = isset($params[$segment]) ? 
          $params[$segment] : 
          $current_request->params[$segment];
        array_push($path, $value);
      } else {
        array_push($path, $segment);
      }

      unset($params[$segment]);
    }
      
    $path = '/' . implode('/', $path);

    if(count($params) > 0) {
      $querystring = array();
      foreach($params as $k => $v) {
        if(isset($this->req[$k]) && $this->req[$k] == $params[$k])
          continue;
        array_push($querystring, "$k=$v");
      }
      if(count($querystring) > 0)
        $path .= '?' . implode('&', $querystring);
    }
       
    return $path;
  }

  public function required_params() {
    if(is_null($this->required_params)) {
      $this->required_params = array_keys($this->req);
      if(!$this->pattern == '')
        foreach(explode('/', $this->pattern) as $segment)
          if($segment[0] == ':')
            $this->required_params[] = ltrim($segment, ':');
    }
    return $this->required_params;
  }

  public static function add($pattern, $params = array()) {
    self::name(null, $pattern, $params);
  }

  public static function name($name, $pattern, $params = array()) {
    array_push(App::$routes, new Route($name, $pattern, $params));
  }

  public static function root($controller, $action = 'index') {
    self::name('root', '/', array(
      'controller' => $controller,
      'action' => $action,
    ));
  }

  public static function defaults() {
    $id_regex = '\d+(-.+)?';
    self::root('home');
    self::add(':controller/:id', array('action' => 'show', 'id' => $id_regex));
    self::add(':controller/:id/:action', array('id' => $id_regex));
    self::add(':controller');
    self::add(':controller/:action/:id');
    self::add(':controller/:action');
  }

}

class Router {

  public static function dispatch($request) {

    # check this request against all available routes
    foreach(App::$routes as $route) {

      if($route->matches_request($request)) {

        # route matched, load the controller and dispatch the request to the
        # appropriate action
        App::$log->request($request);
        App::$log->params($request->params);
        
        $controller = $request->params['controller'];

        # let the Hopnote util know what controller and action we are in
        # for reporting purposes in case an error/exception is encountered
        \Hopnote::$controller = $controller;
        \Hopnote::$action = $request->params['action'];

        # build the controller object and run the action
        $controller_class = Controller::class_name($controller);
        $controller = new $controller_class($request);
        $controller->run();
        return;

      }
    }
    throw new NoMatchingRouteException($request);
  }

}

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

  # cheater method, figure out a better way to do this
  public function title($title) {
    $this->title = $title;
    echo tag('h1', $title);
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
