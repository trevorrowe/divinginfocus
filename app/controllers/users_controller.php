<?php

class UsersController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->add_crumb('Users', array('controller' => 'users'));
    $this->before_filter('load_user', array('except' => 'index'));
  }

  public function index_action($params) {
    $this->users = User::paginate($params->page, 25);
  }

  public function show_action($params) {
    $this->photos = $this->user->photos->paginate(1, 16);
  }

  public function photos_action($params) {
    $this->add_crumb('Photos');
    $this->photos = $this->user->photos->paginate($params->page, 40);
  }

  public function load_user_filter($params) {
    $this->user = User::username_is($params->id)->get();
    $this->add_crumb($this->user->username, $this->user_path($this->user));
  }

}
