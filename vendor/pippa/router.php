<?php

namespace Pippa;

class Router {

  public static function dispatch($request) {

    # check this request against all available routes
    foreach(App::$routes as $route) {

      if($route->matches_request($request)) {

        # route matched, load the controller and dispatch the request to the
        # appropriate action
        App::$log->request($request);
        
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

    # TODO : throw a 404 exception here instead
    die("no possible routes");
  }

}
