<?php

class PhotosController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->before_filter('require_user', array(
      'except' => array('index', 'show'),
    ));
  }

  public function index_action($params) {
    $this->photos = Photo::paginate($params->page);
  }

  public function show_action($params) {
    $this->photo = Photo::get($params->id);
  }

  public function thumbnail_action($p) {
    $uuid = "{$p->a}{$p->b}-{$p->c}-{$p->d}-{$p->e}-{$p->f}{$p->g}{$p->h}";
    $photo = Photo::uuid_is($uuid)->get();
    $thumb = $photo->thumbnail($p->version);
    $thumb->show();
    exit;
  }

  public function new_action($params) {
    $this->photo = new Photo();
  }
  
  public function create_action($params) {

    $this->photo = new Photo($params->photo);
    $this->photo->set_uploaded_file();
    $this->photo->owner_id = App::$session->user_id;

    if($this->photo->save()) {
      $this->flash('notice', 'Photo uploaded successfully'); 
      $this->redirect('show', $this->photo);
    } else {
      $this->flash_now('error', 'Photo upload failed, see errors below.');
      $this->render('new');
    }
  }

}
