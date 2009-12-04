<?php

namespace Pippa;

class Route {

  protected $name;
  protected $pattern;
  protected $req = array();

  protected $match_indexes = array();
  protected $regex;

  public function __construct($pattern, $options = array()) {

    $this->pattern = trim($pattern, '/');

    # all entries in $options are routing requirements except for 'name'
    if(isset($options['name'])) {
      $this->name = $options['name'];
      unset($options['name']);
    }
    $this->req = $options;

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

    if(!Controller::exists($params['controller'])) 
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
    if(!preg_match($this->regex, $request->route_path, $matches))
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
      else
        array_push($regex, '(\w+)');
    }

    $regex = implode('/', $regex);
    $this->regex = "#^$regex$#";

  }

  protected function count_captures($str) {
    return preg_match_all('/\(/', $str, $discard);
  }

  public function matches_params($params) {
    foreach($this->req as $req => $regex) {
      if(!isset($params[$req])) 
        return false;
      if(!preg_match("#^$regex$#", $params[$req])) 
        return false;
    }
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

}
