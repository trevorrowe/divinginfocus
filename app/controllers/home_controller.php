<?php

class HomeController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->before_filter('require_user');
  }

  public function index_action() {
    $this->user = $this->current_user();
  }

}
