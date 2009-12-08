<?php

class IndexController extends ApplicationController {

  public function index_action($params) { 
    #flash('notice', 'Welcome');
  }

  public function redirect_action() {
    $this->redirect('index');
  }

}
