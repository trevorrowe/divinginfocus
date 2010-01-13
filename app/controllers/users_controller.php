<?php

class UsersController extends PublicBaseController {

  public function show_action($params) {
    $this->user = User::username_is($params['username'])->get;
  }

}
