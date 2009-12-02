<?php

namespace Pippa;

class Router {

  protected static $routes = array();

  public static function add_route($pattern, $options = array()) {
    array_push(self::$routes, new Route($pattern, $options));
  }

  public static function dispatch(Request $request) {

    # check this request against all available routes
    foreach(self::$routes as $route) {

      if($route->matches($request)) {

        # route matched, load the controller and dispatch the request to the
        # appropriate action
        
        $controller = $request->params['controller'];

        # let the Hopnote util know what controller and action we are in
        # for reporting purposes in case an error/exception is encountered
        \Hopnote::$controller = $controller;
        \Hopnote::$action = $request->params['action'];

        # build the controller object and run the action
        require_once(Controller::controller_path($controller));
        $controller_class = Controller::class_name($controller);
        $controller = new $controller_class($request);
        $controller->run();
        return;

      }
    }

    # TODO : throw a 404 exception here instead
    die("no possible routes");
  }

}
