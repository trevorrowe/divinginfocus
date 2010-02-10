<?php

namespace Pippa\Exceptions;

class UndefinedHelper extends \Pippa\Exception {

  public function __construct($method) {
    parent::__construct("undefined helper method: $method");
  }

}
