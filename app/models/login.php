<?php

class Login extends \Sculpt\Model {


  public function validate() {
    $this->validate_presence_of('user_id');
  }

  public function user() {
    return User::id_is($this->user_id)->get();
  }

}
