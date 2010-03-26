<?php

class LoginCookie extends \Sculpt\Model {

  ##
  ## validations
  ##

  public function validate() {

    $this->validate_presence_of('username');

    $this->validate_as_uuid('series', 'token');

  }

  ##
  ## associations
  ##

  public static $associations = array(

    'user' => array(
      'type' => 'belongs_to',
      'local_key' => 'username',
      'foreign_key' => 'username',
    ),

  );

  ##
  ## scopes
  ##

  public static function matching_token_scope($scope, $cookie) {
    $scope->where('username = ?', $cookie->username);
    $scope->where('series = ?', $cookie->series);
    $scope->where('token = ?', $cookie->token);
  }

  public static function matching_series_scope($scope, $cookie) {
    $scope->where('username = ?', $cookie->username);
    $scope->where('series = ?', $cookie->series);
  }

  public static function matching_user_scope($scope, $cookie) {
    $scope->where('username = ?', $cookie->username);
  }

}
