<?php

class HomeController extends PublicBaseController {

  public function index_action() {
    $this->photo = Photo::get(42);
  }

}
