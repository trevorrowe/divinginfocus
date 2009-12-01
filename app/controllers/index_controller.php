<?php

class IndexController extends \Pippa\Controller {

  public function index_action($params) {
    trigger_error('foo');
    $foo = array();
    $foo['bar'];
    fatal_test();
  }

}
