<?php

class IndexController extends \Pippa\Controller {

  public function before() {
    $this->set_layout('foo');
  }

  public function index_action($params) {
    #$this->render('list');
    #$this->redirect('foo');
  }

}
