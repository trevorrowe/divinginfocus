<?php

class LoginController extends PublicBaseController {
  
  public function index_action($params, $request) {
    $this->user = new User();
  }

  public function authenticate_action($params) {
    $this->user = new User($params->user, false);
    $username = $this->user->username;
    $password = $this->user->password;
    if($user = User::authenticate($username, $password)) {
      $this->login($user, $params->remember_me);
      if($target = App::$session->pre_login_target) {
        $this->redirect($target);  
        unset(App::$session->pre_login_target);
      }
      else
        $this->redirect('/home');
    } else {
      $this->flash_now('error', 'Invalid username and/or password.');
      $this->render('index');
    }
  }

}
