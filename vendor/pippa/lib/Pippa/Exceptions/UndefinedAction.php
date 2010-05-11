<?php

namespace Pippa\Exceptions;

# produces a 404 page in deployed environments
class UndefinedAction extends \Pippa\Exception {

  public function __construct($request) {

    $cntl = \Pippa\Controller::class_name($request->params['controller']);
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
