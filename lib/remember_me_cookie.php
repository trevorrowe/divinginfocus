<?php

class RememberMeCookie {

  protected static $cookie;

  public static function get() {
    if(is_null(self::$cookie)) {
      self::$cookie = new \Pippa\Cookies\Encrypted(
        'remember_me',
        'hmac_key',
        'secret_key',
        time() + 60 * 60 * 24 * 365, # expire 1 year from now
        '/',                         # path
        '',                          # domain
        false,                       # secure,
        true                         # http_only
      );
    }
    return self::$cookie;
  }

}
