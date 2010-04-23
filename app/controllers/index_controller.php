<?php

class IndexController extends PublicBaseController {

  public function index_action() {
    $this->photo = Photo::first();
  }

}
