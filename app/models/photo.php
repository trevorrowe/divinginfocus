<?php

class Photo extends MediaFile {

  protected $exif;

  ##
  ## associations
  ##

  public static $associations = array(

    'uploader' => array(
      'type' => 'belongs_to',
      'class' => 'User',
      'local_key' => 'username',
      'foreign_key' => 'username'),

    'meta' => array(
      'type' => 'has_one',
      'class' => 'PhotoMeta'),

    'upload_batch' => array(
      'type' => 'belongs_to'),

    'favored_by' => array(
      'type' => 'has_and_belongs_to_many',
      'join_table' => 'photo_favorites',
      'class' => 'User',
      'target_col' => 'username',
      'target_key' => 'username'),

    'comments' => array(
      'type' => 'has_many',
      'class' => 'PhotoComment'),

  );

  ##
  ## validations
  ##

  public function validate() {

    parent::validate();

    $this->validate_format_of('content_type', array(
      'regex' => '/^image\/(jpe?g|png|gif|tiff)$/'
    ));
    
  }

  ##
  ## triggers
  ##

  protected function after_create() {

    parent::after_create();

    # collect some photo meta data

    $exif = $this->exif_data();
    $meta = new PhotoMeta();
    $meta->photo_id = $this->id;
    $meta->width = $exif['COMPUTED']['Width'];
    $meta->height = $exif['COMPUTED']['Height'];

    $exif_keys = array(
      'DateTime' => 'taken_at',
      'Make' => 'camera_make',
      'Model' => 'camera_model',
    );

    foreach($exif_keys as $exif_key => $attr)
      if(isset($exif[$exif_key]))
        $meta->set_attribute($attr, $exif[$exif_key]);

    $meta->savex();

  }

  protected function after_destroy() {

    parent::after_destroy();

    # remove all generated thumbnails and their directories

    $id_path = $this->id_path();
    $dirs = glob(App::root . "/public/photos/versions/**/$id_path");
    foreach($dirs as $dir)
      $this->prune_directory($dir);

  }

  ##
  ## utility methods
  ##

  public function url($version = 'original') {
    if($version == 'original')
      $this->orig_url();
    else
      return "/photos/versions/$version/" . $this->id_path() . '/photo.jpg';
  }

  public function html_title() {
    return "{$this->title} by {$this->username}";
  }

  public function html_alt() {
    return isset($this->caption) ? $this->caption : $this->title;
  }

  public function exif_data() {
    if(is_null($this->exif))
      $this->exif = exif_read_data($this->orig_disk_path());
    return $this->exif;
  }

}
