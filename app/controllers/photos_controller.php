<?php

class PhotosController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->before_filter('require_user', array('only' => 'destroy'));
    $this->before_filter('add_crumbs');
    $this->before_filter('load_photo');
  }

  public function index_action($params) {
    $this->photos = Photo::paginate($params->page, 50);
  }

  public function show_action($params) {
  }

  public function original_action($params) {
    $this->render(false);
  }

  public function edit_action($params) {
    $this->photo = Photo::get($params->id);
    $this->add_crumb('Edit');
  }

  public function update_action($params) {
    $this->photo = Photo::get($params->id);
    if($this->photo->update_attributes($params->photo)) {
      $this->flash('notice', 'Photo updated.');
      $this->redirect('show', $this->photo);
    } else {
      $this->flash_now('error', 'Unable to save changes, see errors below.');
      $this->render('edit');
    }
  }

  public function destroy_action($params) {
    $this->current_user()->photos()->destroy($params->id);
    $this->flash('notice', 'Photo deleted.');
    $this->redirect('index');
  }

  public function add_crumbs_filter($params) {
    if($params->username) {
      $this->add_crumb('Users', '/users');
      $this->add_crumb($params->username, "/users/{$params->username}");
    }
    $this->add_crumb('Photos', '/photos');
  }

  public function load_photo_filter($params) {
    if($params->id) {
      $this->photo = Photo::get($params->id);
      $this->add_crumb($this->photo->title, $this->photo_url($this->photo));
    }
  }

}
