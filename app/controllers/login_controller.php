<?php

class LoginController extends PublicBaseController {
  
  public function index_action($params, $request) {

    $this->user = new User($params->user, false);

    if($request->is_get())
      return;

    if(User::authenticate($this->user)) {
      # TODO : log the user into a session
      flash('notice', "Welcome back, {$this->user->username}");
      $this->redirect('/');
    } else {
      flash_now('error', 'Invalid username and/or password.');
      $this->render('index');
    }
  }

}
