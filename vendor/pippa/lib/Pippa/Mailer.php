<?php

namespace Pippa;

require(\App::root . '/vendor/phpmailer/class.phpmailer.php');

class Mailer extends LocalsContainer {

  protected $from = null;
  protected $reply_to = null;
  protected $to = null;
  protected $cc = null;
  protected $bcc = null;
  protected $subject = null;
  protected $locals = array();

  public static function __callStatic($method, $args) {

    # build the mailer object
    $class = get_called_class();  
    $mailer = new $class();

    # remove the 'deliver_' prefix from $method and call it
    $method = substr($method, 8); 
    call_user_func_array(array($mailer, $method), $args); 

    # now deliver the email
    $mailer->send_email();

  }

  private function render() {
  }

  private function send_email() {

    $mail = new PHPMailer();
    #$mail->isSendmail();

    if(!is_null($this->$from))     $mail->setFrom($this->$from);
    if(!is_null($this->$reply_to)) $mail->addReplyTo($this->$reply_to);
    if(!is_null($this->$to))       $mail->addAddress($this->$to);
    if(!is_null($this->$cc))       $mail->addCC($this->$cc);
    if(!is_null($this->$bcc))      $mail->addBCC($this->$bcc);
    if(!is_null($this->$subject))  $mail->Subject($this->$subject);

    $mail->Body = 'html body';
    $mail->AltBody = 'text body';

    $mail->Send();

  }

}
