<?php 

namespace Pippa;

class Flash {

  const cookie_name = '_pippa_flash';

  public static $data = array();

  protected static $to_expire = array();

  public static function set($key, $payload, $now = false) {
    self::$data[$key] = $payload;
    if($now)
      self::$to_expire[] = $key;
  }

  public static function get($key) {
    return isset(self::$data[$key]) ? self::$data[$key] : null;
  }

  public static function init() {
    if(isset($_COOKIE[self::cookie_name]))
      self::$data = unserialize($_COOKIE[self::cookie_name]);
    self::$to_expire = array_keys(self::$data);
  }

  public static function clean() {
    foreach(self::$to_expire as $key)
      unset(self::$data[$key]);
    $data = serialize(self::$data);
    # TODO : get the current domain
    $domain = '.' . $_SERVER['HTTP_HOST'];
    setcookie(self::cookie_name, $data, 0, '/', $domain, false, true);
  }

}
