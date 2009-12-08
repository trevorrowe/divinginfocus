<?php

class IndexController extends ApplicationController {

  public function index_action($params) { 
    #User::foo();
    #flash('notice', 'Welcome');
    #flash_now('error', 'Unable to save changes, see errors below.');
  }

  public function redirect_action() {
    flash('notice', 'you got redirected');
    $this->redirect('index');
  }

}
