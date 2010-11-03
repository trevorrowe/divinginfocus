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
    $this->photos = $this->user->photos->paginate(1, 20);
    $this->videos = $this->user->videos->paginate(1, 10);
    $this->albums = $this->user->albums->paginate(1, 10);
  }

  public function photos_action($params) {
    $this->add_crumb('Photos');
    $this->photos = $this->user->photos->paginate($params->page, 50);
  }

  public function videos_action($params) {
    $this->add_crumb('Videos');
    $this->videos = $this->user->videos->paginate($params->page, 15);
  }

  public function load_user_filter($params) {
    $this->user = User::username_is($params->username)->get();
    $this->add_crumb($this->user->username, $this->user_path($this->user));
  }

}
