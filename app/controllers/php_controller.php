<?php

class PhpController extends ApplicationController {

  public function info_action($params) {
    $this->render_text(phpinfo(), array('layout' => false));
  }

}
