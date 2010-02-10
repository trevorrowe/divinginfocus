<?php

namespace Pippa;

require(App::root . '/vendor/phpmailer/class.phpmailer.php');

class Mailer {

  protected static $from = null;
  protected static $reply_to = null;
  protected static $to = null;
  protected static $cc = null;
  protected static $bcc = null;
  protected static $subject = null;
  protected static $locals = array();

  public static function __callStatic($method, $args) {

    self::clear();

    $method = substr($method, 8); # remove the 'deliver_' prefix from $method
    $class = get_called_class();  # so we can forward $method to the right class
    forward_static_call_array(array($class, $method), $args);

    self::send_email();

  }

  private static function clear() {
    self::$from = null;
    self::$reply_to = null;
    self::$to = null;
    self::$cc = null;
    self::$bcc = null;
    self::$subject = null;
    self::$locals = array();
  }

  private static function send_email() {

    $mail = new PHPMailer();
    #$mail->isSendmail();

    if(!is_null(self::$from)) $mail->setFrom(self::$from);
    if(!is_null(self::$reply_to)) $mail->addReplyTo(self::$reply_to);
    if(!is_null(self::$to)) $mail->addAddress(self::$to);
    if(!is_null(self::$cc)) $mail->addCC(self::$cc);
    if(!is_null(self::$bcc)) $mail->addBCC(self::$bcc);
    if(!is_null(self::$subject)) $mail->Subject(self::$subject);

    $mail->send();

  }

}
