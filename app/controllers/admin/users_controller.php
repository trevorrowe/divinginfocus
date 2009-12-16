<?php

class Admin_UsersController extends Admin_BaseController {

  public function index_action($params) {
    $page = isset($params['page']) ? $params['page'] : 1;
    $this->locals['users'] = User::find()->paginate($page);
    #$this->locals['users'] = User::find()->all();
    #$this->locals['users'] = User::find()->other->paginate($page);
    #$this->locals['users'] = User::find()->cool->asc_by_username->paginate($page);
  }

  public function show_action($params) {
    $this->locals['user'] = User::get($params['id']);
  }

  public function new_action($params) {
    $this->locals['user'] = new User();
  }

  public function create_action($params) {
    $user = new User($params['user']);
    if($user->save()) {
      flash('notice', 'User added.');
      $this->redirect('show', $user);
    } else {
      flash('error', 'Unable to add user, see errors below.');
      $this->locals['user'] = $user;
      $this->render('new');
    }
  }

  public function edit_action($params) {
    $this->locals['user'] = User::get($params['id']);
  }

  public function update_action($params) {
    $user = User::get($params['id']);
    if($user->update_attribute($params['user'])) {
      flash('notice', 'User updated.');
      $this->redirect('show', $user);
    } else {
      flash('error', 'Unable to update user, see errors below.');
      $this->locals['user'] = $user;
      $this->render('edit');
    }
  }

  public function destroy_action($params) {
    $user = User::get($params['id']);
    $user->destroy();
    flash('notice', 'User removed.');
    $this->redirect('index');
  }

}
