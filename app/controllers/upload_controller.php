<?php

class UploadController extends PublicBaseController {

  public function init() {
    parent::init();
    #$this->before_filter('require_user');
  }

  public function index_action($params) {
    $this->photo = new Photo();
  }

  public function form_upload_action($params) {

    $this->photo = new Photo($params->photo);
    $this->photo->set_uploaded_file();
    $this->photo->owner_id = App::$session->owner_id;

    if($this->photo->save()) {
      $this->flash('notice', 'Photo uploaded successfully'); 
      $this->redirect('photos', 'show', $this->photo);
    } else {
      $this->flash_now('error', 'Photo upload failed, see errors below.');
      $this->render('index');
    }
  }

  public function flash_upload_action($params) {
    $photo = new Photo();
    $photo->set_uploaded_file('Filedata');
    $photo->owner_id = 23;
    $photo->savex();
    if($photo->save()) {
      $url = $photo->url('medium');
      $this->render_text("{ 'filename' : 'asdf', 'url' : '$url' }");
    } else {
      $this->status(400); # bad request
      $this->render_text("{ 'error' : 'errors go here' } ");
    }
  }

}
