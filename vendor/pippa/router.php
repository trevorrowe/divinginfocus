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
