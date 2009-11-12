<?php

class IndexController extends \Framework\Controller {

  public function before() {
    $this->set_layout('foo');
  }

  public function index_action($params) {
    #$this->render('list');
    #$this->redirect('foo');
  }

}
