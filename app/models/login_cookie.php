<?php

class LoginCookie extends \Sculpt\Model {

  ##
  ## validations
  ##

  public function validate() {

    $this->validate_presence_of('user_id');

    $this->validate_as_uuid('series', 'token');

  }

  ##
  ## associations
  ##

  public function user() {
    return User::get($this->user_id);
  }

  ##
  ## scopes
  ##

  public static function matching_token_scope($scope, $cookie) {
    $scope->where('user_id = ?', $cookie->user_id);
    $scope->where('series = ?', $cookie->series);
    $scope->where('token = ?', $cookie->token);
  }

  public static function matching_series_scope($scope, $cookie) {
    $scope->where('user_id = ?', $cookie->user_id);
    $scope->where('series = ?', $cookie->series);
  }

  public static function matching_user_scope($scope, $cookie) {
    $scope->where('user_id = ?', $cookie->user_id);
  }

}
