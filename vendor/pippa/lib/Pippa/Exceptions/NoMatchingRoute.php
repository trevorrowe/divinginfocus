<?php

namespace Pippa\Exceptions;

# produces a 404 page in deployed environments
class NoMatchingRoute extends \Pippa\Exception {

  public function __construct($request) {
    $msg = "No route matches '{$request->uri}' with method {$request->method}";
    parent::__construct($msg);
  }

}
