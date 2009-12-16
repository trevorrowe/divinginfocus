<?php

class User extends \Sculpt\Model {

  static $secret = '9c3ea1e36407b1f3fab4c43e5b4277ff87f62e6a35b2a72';

  static $attr_accessors = array('password', 'password_confirmation');

  static $attr_whitelist = array('email', 'password', 'password_confirmation');

  static $scopes = array(
    #'admin' => array('where' => 'admin = 1'),
    #'admin' => array('where' => array('admin = ?', true)),
    'admin' => array('where' => array('admin' => true)),
    'activated' => array('where' => 'activated_at IS NOT NULL'),
    'cool' => array('activated', 'admin', 'order' => 'username DESC'),
  );

  public static function other_scope($scope) {
    $scope->admin->activated->order('username ASC');
  }

  protected function _set_password($password) {
    $salt = '';
    for($i = 0; $i < 24; ++$i)
      $salt .= chr(rand(33,126));
    $this->password_salt = $salt;
    $this->password_hash = hash('sha256', $password . $salt . self::$secret);
    $this->_set('password', $password);
  }

}
