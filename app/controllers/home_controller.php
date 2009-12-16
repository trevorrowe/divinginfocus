<?php

class HomeController extends PublicBaseController {

  public function index_action($params) { 
    $user = User::find()->admin->first();

    // $columns = User::columns();
    // $columns = Photo::columns();
    // $columns = Video::columns();
    // 
    // $user = new User();
    // $user->username = 'trevorrowe';
    // $user->admin = '1';
    // $user->uuid = '12345-12345679-1234-1234';
    // $user->set_attributes(array(
    //   'email' => 'trevorrowe@gmail.com',
    //   'password' => 'asdf',
    //   'password_confirmation' => 'asdf',
    // ));
    #$user->save();

    #debug($user->admin, false);
    #debug($user->attribute_before_type_cast('admin'), false);
    #debug($user->attributes(), false);
    #exit;
  }

  public function redirect_action() {
    flash('notice', 'you got redirected');
    $this->redirect('index');
  }

}
