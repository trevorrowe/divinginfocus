<?php

class Admin_BaseController extends ApplicationController {

  static $layout = 'admin';

  public function __construct($request) {
    parent::__construct($request);
    $this->add_helper('admin');
  }

}
