<?php

class Admin_BaseController extends ApplicationController {

  public function init() {
    parent::init();
    $this->layout('admin');
    $this->before_filter('require_admin');
  }

}
