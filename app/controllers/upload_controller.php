<?php

class UploadController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->before_filter('require_user', array('except' => 'flash_upload'));
  }

  public function index_action($params) {

    $this->photo = new Photo();

    $batch = new UploadBatch();
    $batch->username = $this->current_user()->username;
    $batch->savex();
    $this->batch = $batch;

  }

  public function form_upload_action($params) {

    if(!isset($_FILES['file'])) {
      $this->redirect('upload');
      return;
    }

    if(substr($_FILES['file']['type'], 0, 5) == 'video') {
      $this->file = new Video();
      $type = 'Video';
    } else {
      $this->file = new Photo();
      $type = 'Photo';
    }

    $this->file->set_uploaded_file();
    $this->file->username = $this->current_user()->username;
    if($this->file->save()) {
      $this->flash('notice', "$type uploaded successfully"); 
      $this->redirect(pluralize(underscore($type)), 'show', $this->file);
    } else {
      $this->flash_now('error', "$type upload failed, see errors below.");
      $this->render('index');
    }
  }

  public function flash_upload_action($params) {

    $batch = UploadBatch::uuid_is($params->batch)->get();

    $photo = new Photo();
    $photo->set_uploaded_file('Filedata');
    $photo->upload_batch_id = $batch->id;
    $photo->username = $batch->username;

    if($photo->save()) {
      $url = $photo->url('medium');
      $this->render_text("{ 'filename' : 'asdf', 'url' : '$url' }");
    } else {
      $this->status(400); # bad request
      $this->render_text("{ 'error' : 'errors go here' } ");
    }
  }

}
