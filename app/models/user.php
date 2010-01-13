<?php

class User extends \Sculpt\Model {

  static $secret = '9c3ea1e36407b1f3fab4c43e5b4277ff87f62e6a35b2a72';

  static $attr_accessors = array('password', 'password_confirmation');

  static $attr_whitelist = array('email', 'password', 'password_confirmation');

  ##
  ## validations
  ##

  protected function validate() {

    $this->validate_presence_of(
      'username', 'email', 'password_salt', 'password_hash'
    );

    $this->validate_format_of('username', array(
      'regex' => '/^[a-zA-Z0-9]{2,30}$/',
      'message' => 'must be 2 to 30 letters and numbers',
      'allow_null' => true,
    ));

    $this->validate_uniqueness_of('username', 'email', array(
      'case_sensitive' => false,
      'allow_null' => true,
    ));

    $this->validate_format_of('email', array(
      'regex' => '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',
      'message' => 'must be a valid email address',
      'allow_null' => true,
    ));

    $this->validate_as_boolean('admin');

    $this->validate_as_uuid('uuid');

    $this->validate_presence_of('password', array('on' => 'create'));

    $this->validate_confirmation_of('password');

    $this->validate_length_of('password_salt', array('is' => 32));

    $this->validate_length_of('password_hash', array('is' => 64));

  }

  ##
  ## callbacks
  ##

  protected function before_validate_on_create() {
    $this->uuid = uuid();
  }

  ##
  ## associations
  ##

  static $has_one = array(
    array('profile'),
  );

  static $has_many = array(
    array('photos'),
    array('vidoes'),
    array('albums'),
    array('photo_comments'),
    array('video_comments'),
    array('album_comments'),
    array('logins'),
    array('login_cookies'),
  );

  static $has_and_belongs_to_many = array(
    array('favorite_photos'),
    array('favorite_videos'),
    array('favorite_albums'),
    array('favorite_users'),
  );

  ##
  ## scopes
  ##

  public static $scopes = array(
    'admin' => array('admin' => true),
    'activated' => array('where' => 'activated_at IS NOT NULL'),
  );

  public static function other_scope($scope) {
    $scope->admin->activated->order('username ASC');
  }

  ##
  ## setters & getters
  ##

  protected function _set_password($password) {
    $salt = '';
    for($i = 0; $i < 32; ++$i)
      $salt .= chr(rand(33,126));
    $this->password_salt = $salt;
    $this->password_hash = hash('sha256', $password . $salt . self::$secret);
    $this->_set('password', $password);
  }

  ##
  ## utility methods
  ##

  public function randomize_password() {

    $word = array_rand(array(
      'scuba', 'dive', 'diving', 'fish', 'photo', 'camera', 'digital',
      'water', 'underwater', 'focus', 'shot', 'flash', 'strobe',
      'view', 'vista', 'observe', 'sculpin', 'gunnel', 'rockfish',
      'nudi', 'gpo', 'octo', 'snorkel', 'mask', 'fins', 'compass', 'tank',
      'bcd', 'backplate', 'wing', 'eel', 'crab', 'coral', 'album',
      'current', 'drift', 'deep', 'lens', 'macro', 'hd', 'video', 'movie',
      'report', 'backscatter', 'squeeze', 'pressure', 'pst', 'steel',
      'lumpie', 'sink', 'float', 'smb', 'buoyancy', 'singal', 'neutral',
      'trim', 'relax', 'co2', 'oxygen', 'nitrogen', 'silent', 'nitrox',
      'helitrox', 'helium', 'narcosis', 'bubbles', 'below', 'slave',
    ));

    $random_password = $word . rand(1,1000);
    $this->password = $random_password;
    $this->password_confirmation = $random_password;
  }

}
