<?php

class LoginController extends PublicBaseController {
  
  public function index_action($params, $request) {

    if($request->method == 'POST') {
      if($user = User::username_is($params['user']['username'])->first) {
      }
    }

    if(is_null($user))
      $user = new User($params['user'], false);

    $this->user = $user;

  }

}
