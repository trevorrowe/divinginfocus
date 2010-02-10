<?php

namespace Pippa\Cookies;

class Encrypted extends TamperProof {

  protected $secret_key;
  #protected $td;
  #protected $ks;
  #protected $iv;

  public function __construct($name, $hmac_key, $secret_key, 
    $expire = 0, $path = '', $domain = '', $secure = '')
  {
    $this->secret_key = $secret_key;
    parent::__construct($name, $hmac_key, $expire, $path, $domain, $secure);
  }

  protected function marshall($cookie) {
    return parent::marshall($cookie);
  }

  protected function unmarshall($cookie) {
    return parent::unmarshall($cookie);
  }

}
