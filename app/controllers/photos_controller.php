<?php

class PhotosController extends MediaBaseController {

  public function index_action($params) {
    $this->photos = Photo::desc_by_created_at()->paginate($params->page, 50);
    $this->add_crumb('Photos', '/photos');
  }

  public function edit_action($params) {
    $this->add_crumb('Edit', $this->media_url($this->photo, 'edit'));
  }

  public function update_action($params) {
    if($this->photo->update_attributes($params->photo)) {
      $this->flash('notice', 'Photo updated.');
      $this->redirect($this->media_url($this->photo));
    } else {
      $this->flash_now('error', 'Unable to save changes, see errors below.');
      $this->render('edit');
    }
  }
  
  public function favorite_action($params) {
    $this->current_user()->favorite_photos->add($this->photo);
    $this->redirect($this->media_url($this->photo));
  }

  public function unfavorite_action($params) {
    #$this->current_user()->favorite_photos->id_is($params->id)->clear();
    $this->current_user()->favorite_photos->remove($this->photo);
    $this->redirect($this->media_url($this->photo));
  }

  public function destroy_action($params) {
    $this->current_user()->photos->destroy($params->id);
    $this->flash('notice', 'Photo deleted.');
    $this->redirect('index');
  }

}
