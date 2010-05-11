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

    $sudoer_username = App::$session->sudoer_username ? 
      App::$session->sudoer_username :
      $this->current_user()->username;

    # get the target user
    $target = User::username_is($params->username)->active->get();

    # clear the current session and set only the sudoer_username and user_id
    App::$session->clear();
    if($sudoer_username != $target->username)
      App::$session->sudoer_username = $sudoer_username;
    App::$session->username = $target->username;
    App::$session->timestamp = time();

    $this->flash('notice', "You are now logged in as {$target->username}.");
    $this->redirect('/home');

  }

}
