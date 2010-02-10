<?php

class SignupController extends PublicBaseController {
  
  public function index_action($params, $request) {

    $this->user = new User();

    if($request->is_get())
      return;

    # log the user out of their session before they create a new account
    $this->logout();

    $this->user->set_attributes($params->user);
    $this->user->username = $params->user->username;

    if($this->user->save()) {
      UserMailer::deliver_validation($this->user);
      $msg = "A verification email has been sent to {$this->user->email}.";
      $this->flash('notice', $msg);
      $this->redirect('verify_email');
    } else {
      $this->flash_now('error', 'Signup failed, see errors below.');
    }
  }

  public function resend_verification_email_action($params) {
  }

  public function verify_email_action($params) {

    if($params->code) {
      # TODO : find the pending account to verify
    }

  }

}
