<?php

class IndexController extends \Pippa\Controller {

  public function index_action($params) { 
    $this->locals['foo'] = 'bar';
  }

  public function redirect_action() {
    $this->redirect('index');
  }

}
