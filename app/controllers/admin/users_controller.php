<?php

class Admin_UsersController extends \Pippa\Controller {

  public function index() {
    $this->render_text(phpinfo(), array('layout' => false));
  }

}
