<?php

namespace Pippa\Cookies;

class TamperProof extends Basic {

  protected $hmac_key;

  public function __construct($name, $hmac_key, 
    $expire = 0, $path = '/', $domain = '', $secure = '')
  {
    $this->hmac_key = $hmac_key;
    parent::__construct($name, $expire, $path, $domain, $secure, true);
  }

  protected function marshall($cookie) {
    $cookie = parent::marshall($cookie);
    return $this->hash($cookie) . $cookie;
  }

  protected function unmarshall($cookie) {

    $hmac = substr($cookie, 0, 64);
    $cookie = substr($cookie, 64);

    # ensure the cookie wasn't tampered with, if so, return an empty array
    if($this->hash($cookie) != $hmac)
      return array();

    # the cookie past the hmac, finsh unpacking it
    return parent::unmarshall($cookie);

  }

  protected function hash($cookie) {
    return hash_hmac('sha256', $cookie, $this->hmac_key, false);
  }

}
