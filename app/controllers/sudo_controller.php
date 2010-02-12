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

    # get the id of the user that is sudoing
    $sudo_id = App::$session->sudo_id ? 
      App::$session->sudo_id :
      $this->current_user()->id;

    # get the target user
    $target = User::active()->id_is($params->id)->get();

    # clear the current session and set only the sudo_id and user_id
    App::$session->clear();
    if($sudo_id != $target->id)
      App::$session->sudo_id = $sudo_id;
    App::$session->user_id = $target->id;
    App::$session->timestamp = time();

    $this->flash('notice', "You are now logged in as {$target->username}.");
    $this->redirect('/');

  }

}
