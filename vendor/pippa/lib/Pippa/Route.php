<?php

namespace Pippa;

class Route {

  protected $name;

  protected $pattern;

  protected $regex;

  protected $req = array();

  protected $required_params;

  protected $match_indexes = array();

  public function __construct($name, $pattern, $params = array()) {

    # move the format from the pattern into the requirements
    if(preg_match('/^(.+)\.(\w+)$/', $pattern, $matches)) {
      $pattern = $matches[1];
      $params['format'] = $matches[2];
    }

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

    if(!in_array($params['controller'], \App::$cache->controller_names))
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
        if(is_null($v))
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
    array_push(\App::$routes, new Route($name, $pattern, $params));
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
    //self::add(':controller/:id/:action', array('id' => $id_regex));
    self::add(':controller');
    self::add(':controller/:action/:id');
    self::add(':controller/:action');
  }

}
