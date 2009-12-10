<?php

class IndexController extends ApplicationController {

  public function index_action($params) { 
  }

  public function redirect_action() {
    flash('notice', 'you got redirected');
    $this->redirect('index');
  }

}
