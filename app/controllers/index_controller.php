<?php

class IndexController extends \Pippa\Controller {

  public function index_action($params) { }

  public function redirect_action() {
    $this->redirect('index', 'index', 123, array('status' => 307));
  }

}
