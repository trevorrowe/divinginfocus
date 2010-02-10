<?php

class UserMailer extends \Pippa\Mailer {

  public static function validation(User $user) {
    self::$subject = '[Diving in Focus] Account Activation';
    self::$from    = 'noreply@divinginfocus.com';
    self::$to      = $user->email;
    self::$bcc     = 'trevorrowe@gmail.com';
    self::$locals  = array('user' => $user);
  }

  public static function password_reset(User $user) {
    self::$subject = '[Diving in Focus] Password Reset';
    self::$from    = 'noreply@divinginfocus.com';
    self::$to      = $user->email;
    self::$locals  = array('user' => $user);
  }

}
