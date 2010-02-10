<?php

class LogoutController extends PublicBaseController {
  
  public function index_action() {
    $this->logout();
    $this->redirect('/');
  }

}
