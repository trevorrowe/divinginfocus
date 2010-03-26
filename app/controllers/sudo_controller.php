<?php

class SudoController extends ApplicationController {

  ##
  ## filters
  ##

  public function init() {
    parent::init();
    $this->layout('admin');
    $this->before_filter('require_sudoer');
  }

  ##
  ## actions
  ##
  
  public function index_action($params) {
    $this->users = User::active()->paginate($params->page);
  }

  public function login_as_action($params) {

    $username = $params->id;

    # get the id of the user that is sudoing
    $sudo_username = App::$session->sudo_username ? 
      App::$session->sudo_username :
      $this->current_user()->username;

    # get the target user
    $target = User::active()->username_is($username)->get();

    # clear the current session and set only the sudo_username and user_id
    App::$session->clear();
    if($sudo_username != $target->username)
      App::$session->sudo_username = $sudo_username;
    App::$session->user_id = $target->id;
    App::$session->timestamp = time();

    $this->flash('notice', "You are now logged in as {$target->username}.");
    $this->redirect('/');

  }

}
