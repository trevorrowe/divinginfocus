<?php

class IndexController extends \Pippa\Controller {

  public function index_action($params) {
    $foo = array();
    $foo['bar'];
    fatal_test();
  }

}
