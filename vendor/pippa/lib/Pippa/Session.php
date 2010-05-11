<?php

namespace Pippa;

class Session extends Cookies\Encrypted {

  public function __construct() {
    # TODO : config this
    parent::__construct(
      '_app_session',
      'c198c701f9e0452ab7d9711512fc02f6375a59b6c83ad82fca64463da5f4da27',
      '72b655188071ae33400002e9d1e11e103c98b071e9d99e1cbc50c94f7ef694fb'
    );
  }

}
