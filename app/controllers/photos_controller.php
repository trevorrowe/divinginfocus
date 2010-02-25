<?php

class PhotosController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->before_filter('require_user', array(
      'only' => 'destroy',
    ));
  }

  public function index_action($params) {
    $this->photos = Photo::paginate($params->page);
  }

  public function show_action($params) {
    $this->photo = Photo::get($params->id);
  }

  public function destroy_action($params) {
    $this->current_user()->photos()->destroy($params->id);
    $this->flash('notice', 'Photo deleted.');
    $this->redirect('index');
  }

}
