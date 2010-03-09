<?php

class PublicBaseController extends ApplicationController {

  static $layout = 'public';

  public function init() {
    parent::init();
    $this->add_crumb('Diving in Focus', '/');
  }

}
