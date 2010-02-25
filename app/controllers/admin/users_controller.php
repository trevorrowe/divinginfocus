<?php

class Admin_UsersController extends Admin_BaseController {

  public function index_action($params) {
    $this->users = User::paginate($params->page);
  }

  public function show_action($params) {
    $this->user = User::get($params->id);
  }

  public function new_action($params) {
    $this->user = new User();
  }

  public function create_action($params) {
    $user = new User($params->user, false);
    if(!$user->password)
      $user->randomize_password();
    if($user->save()) {
      $this->flash('notice', 'User added.');
      $this->redirect('show', $user);
    } else {
      $this->flash_now('error', 'Unable to add user, see errors below.');
      $this->flash_now('error', $user->errors->full_messages());
      $this->user = $user;
      $this->render('new');
    }
  }

  public function edit_action($params) {
    $this->user = User::get($params->id);
  }

  public function update_action($params) {
    $user = User::get($params->id);
    $user->set_attributes($params->user, false);
    if($user->update_attributes($params->user, false)) {
      $this->flash('notice', 'User updated.');
      $this->redirect('show', $user);
    } else {
      $this->flash('error', 'Unable to update user, see errors below.');
      $this->user = $user;
      $this->render('edit');
    }
  }

  public function destroy_action($params) {
    $user = User::get($params->id);
    $user->destroy();
    $this->flash('notice', 'User deleted.');
    $this->redirect('index');
  }

}
