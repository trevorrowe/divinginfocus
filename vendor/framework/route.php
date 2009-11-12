<?php

namespace Framework;

class Route {

  protected $path;
  protected $name = NULL;
  protected $req = array();

  # controller
  # action
  # method
  # format

  public function __construct($path, $opts = array()) {

    $this->path = trim($path, '/');

    # the $opts hash all represent requirements excep the 'name' option
    if(isset($opts['name'])) {
      $this->name = $opts['name'];
      unset($opts['name']);
    }

    $this->req = $opts;

    foreach(array('method', 'format', 'controller', 'action') as $r)
      $this->req[$r] = isset($opts[$r]) ? $opts[$r] : NULL;
  
    # ensure the controller and action are present as path segments or
    # as required values, but not both
    foreach(array('controller', 'action') as $opt) {
      if($this->req[$opt] && preg_match("/:$opt/", $this->path)) {
        $err = "Invalid route, `$opt` may be provided as a route segment " . 
               'or as a requirement, but not both.';
        throw new \Exception($err);
      }
      # if controller or action are not defined as either a path segment or
      # a requirement we will set them as requirements to 'index'
      if(!$this->req[$opt] && !preg_match("/:$opt/", $this->path))
        $this->req[$opt] = 'index';
    }

  }

  public function matches(Request $request) {
    if(!$this->matchesMethod($request)) return false;
    if(!$this->matchesFormat($request)) return false;
    if(!($this->matchesPath($request))) return false;
    return Controller::exists($request->params['controller']);
  }

  protected function matchesMethod(Request $r) {
    return $this->req['method'] ? $this->req['method'] == $r->method : true;
  }

  protected function matchesFormat(Request $request) {
    return $this->req['format'] ? $this->req['format'] == $r->format : true;
  }

  protected function matchesPath(Request $request) {

    $match_index = 1;
    $match_indexes = array();

    # build a regex based on the route path and requirements
    $regex = array();
    foreach(explode('/', $this->path) as $segment) {

      if($segment == '') continue;

      if($segment[0] != ':') {
        array_push($regex, $segment);
        continue;
      }

      # strip the leading : from the segment name
      $segment = substr($segment, 1);

      # keep track of where in the array of regex matches this segment
      # will store its value.
      $match_indexes[$match_index++] = $segment;

      if(isset($this->req[$segment])) {
        $requirement = $this->req[$segment];
        $requirement = preg_replace('/\./', '[^/]', $requirement);
        array_push($regex, "($requirement)");
        $match_index += preg_match_all('/\(/', $requirement, $discard);
      }
      else if($segment == 'controller')
        array_push($regex, '([a-z][/a-z]*)');
      else
        array_push($regex, '([^/]+)');
    }
    $regex = implode('/', $regex);
    $regex = "#^$regex$#";

#echo "test: '{$request->path}' against '{$this->path}' with $regex\n";
    if(!preg_match($regex, trim($request->path, '/'), $matches)) {
#  echo "fail: regex\n";
      return false;
    }

    $params = array();
    foreach($match_indexes as $i => $name)
      $params[$name] = $matches[$i];

    foreach(array('controller', 'action', 'format') as $r)
      if($this->req[$r]) $params[$r] = $this->req[$r];

    foreach($params as $k => $v)
      $request->params[$k] = $v;

    return true;
  }

  public function buildPath($params) {

    $path = array();

    foreach(explode('/', $this->path) as $segment) {
      if($segment == '') continue;
      # TODO : handle the '' path route
      if($segment[0] == ':') {
        $segment = substr($segment, 1);
        array_push($path, $params[$segment]);
      } else {
        array_push($path, $segment);
      }
      unset($params[$segment]);
    }
      
    $path = '/' . implode('/', $path);

    if(count($params) > 0) {
      $querystring = array();
      foreach($params as $k => $v) {
        if($params[$k] == $this->req[$k]) continue;
        array_push($querystring, "$k=$v");
      }
      if(count($querystring) > 0)
        $path .= '?' . implode('&', $querystring);
    }
       
    return $path;
  }

  public function testParams($params) {
    foreach($this->req as $req => $regex) {
      if(!$regex) continue;
      if(!isset($params[$req])) return false;
      if(!preg_match("#^$regex$#", $params[$req])) return false;
    }
    return true;
  }

  public function params() {
    return $this->defaults;
  }

}
