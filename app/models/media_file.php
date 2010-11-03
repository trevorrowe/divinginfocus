<?php

abstract class MediaFile extends \Sculpt\Model {

  public static $table_name = 'media_files'; 

  protected $upload_error_code;

  protected $tmp_filename;

  ##
  ## validations
  ##

  public function validate() {

    $this->validate_length_of('filename', array('maximum' => 255));

    $this->validate_length_of('title', array(
      'maximum' => 100,
      'allow_null' => true,
    ));

    $this->validate_length_of('caption', array(
      'maximum' => '2000',
      'allow_null' => true,
    ));

  }

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
      'join_table' => 'media_favorites',
      'class' => 'User',
      'target_col' => 'username',
      'target_key' => 'username'),

    'comments' => array(
      'type' => 'has_many',
      'class' => 'PhotoComment'),

  );

  ##
  ## triggers
  ##

  protected function before_create() {
    $this->type = strtolower(get_called_class());
  }

  protected function after_create() {

    # move the uploaded file from the webserver location

    $target = $this->orig_disk_path();
    $dir = dirname($target);
    if(!file_exists($dir))
      mkdir($dir, 0777, true);
    move_uploaded_file($this->tmp_filename, $target);

  }

  protected function after_destroy() {

    # delete original file from disk

    $type_dir = static::plural_type();
    $dir = App::root . "/public/$type_dir/originals/" . $this->id_path();
    $this->prune_directory($dir);

  }

  ##
  ## utility methods
  ##

  public function _title() {
    if($title = $this->_get('title'))
      return $title;
    $filename_parts = explode('.', $this->filename);
    return titleize($filename_parts[0]);
  }

  abstract public function url();

  public function orig_url() {
    $type = static::plural_type();
    return "/{$type}/originals/" . $this->id_path() . "/{$this->filename}";
  }

  public function orig_disk_path() {
    return App::root . '/public' . $this->orig_url();
  }

  # Returns the id in a disk path that limits the number of folders per
  # directory to 999.  The database id of 123456789 would be returned as
  # 
  #   '000/123/456/789'
  #
  protected function id_path() {
    return implode('/', str_split(str_pad($this->id, 12, 0, STR_PAD_LEFT), 3));
  }

  #abstract public function set_file($path);

  public function set_uploaded_file($key = 'file') {

    $this->filename = $_FILES[$key]['name'];
    $this->size = $_FILES[$key]['size'];
    $this->tmp_filename = $_FILES[$key]['tmp_name'];

    $error = $_FILES[$key]['error'];
    $this->upload_error_code = $error ? $error : null;

    # don't trust the browser-posted content-type, get the real content-type
    if($this->tmp_filename) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $this->content_type = finfo_file($finfo, $this->tmp_filename);
      finfo_close($finfo);
    }

  }

  public static function type() {
    return strtolower(get_called_class());
  }

  public static function plural_type() {
    return static::type() . 's';
  }

  protected static function prune_directory($dir) {
    $type_dir = static::plural_type();
    do {
      exec("rm -rf $dir");
      $dir = dirname($dir);
      $files = scandir($dir); # empty directories have 2 files, . and ..
    } while(basename($dir) != $type_dir && count($files) == 2);
  }

}
