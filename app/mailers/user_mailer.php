<?php

class UserMailer extends \Pippa\Mailer {

  public function validation(User $user) {
    $this->subject = '[Diving in Focus] Account Activation';
    $this->from    = 'noreply@divinginfocus.com';
    $this->to      = $user->email;
    $this->bcc     = 'trevorrowe@gmail.com';
    $this->locals  = array('user' => $user);
  }

  public function password_reset(User $user) {
    $this->subject = '[Diving in Focus] Password Reset';
    $this->from    = 'noreply@divinginfocus.com';
    $this->to      = $user->email;
    $this->locals  = array('user' => $user);
  }

}
