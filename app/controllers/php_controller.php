<?php

class PhpController extends \Pippa\Controller {

  public function info_action($params) {
    $text = phpinfo();
    $this->render_text($text, array('layout' => false));
  }

}
