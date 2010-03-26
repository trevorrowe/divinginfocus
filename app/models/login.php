<?php

class Login extends \Sculpt\Model {


  public function validate() {
    $this->validate_presence_of('username');
  }

  public static $associations = array(

    'user' => array(
      'type' => 'belongs_to',
      'local_key' => 'username',
      'foreign_key' => 'username',
    ),

  );

}
