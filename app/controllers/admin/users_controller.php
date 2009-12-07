<?php

class Admin_UsersController extends ApplicationController {

  public function index() {
    $this->render_text(phpinfo(), array('layout' => false));
  }

}
