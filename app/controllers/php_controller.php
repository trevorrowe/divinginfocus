<?php

class PhpController extends \Pippa\Controller {

  public function info_action($params) {
    $this->render_text(phpinfo(), array('layout' => false));
  }

}
