<?php 

namespace Pippa;

# TODO : Pippa\Flash should extend / use Pippa\Cookie\TamperProof
class Flash extends Cookies\Encrypted {

  protected $to_expire = array();

  public function __construct() {
    # TODO : config this
    parent::__construct(
      '_app_flash',
      'c198c701f9e0452ab7d9711512fc02f6375a59b6c83ad82fca64463da5f4da27',
      '72b655188071ae33400002e9d1e11e103c98b071e9d99e1cbc50c94f7ef694fb'
    );
    
    $this->to_expire = array_keys($this->data);
  }

  public function save($expire = null) {
    foreach($this->to_expire as $key)
      unset($this->data[$key]);
    parent::save($expire);
  }

  public function set_to_expire($key) {
    $this->to_expire[] = $key;
  }

}
