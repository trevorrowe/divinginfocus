<?php

namespace Pippa;

class Router {

  protected static $routes = array();

  public static function add_route($pattern, $options = array()) {
    array_push(self::$routes, new Route($pattern, $options));
  }

  public static function dispatch(Request $request) {
    foreach(self::$routes as $route) {
      if($route->matches($request)) {

        $controller = $request->params['controller'];

        \Hopnote::$controller = $controller;
        \Hopnote::$action = $request->params['action'];

        require_once(Controller::controller_path($controller));

        $controller_class = Controller::class_name($controller);
        $controller = new $controller_class($request);
        $controller->run();
        return;
      }
    }
    # TODO : throw 404
    die("no possible routes");
  }

}
