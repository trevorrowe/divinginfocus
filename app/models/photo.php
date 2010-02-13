<?php

class Photo extends \Sculpt\Model {

  protected static $versions = array(
    'thumb'  => array('ar' => array(128,96)),
    'small'  => array('resize' => array(300, 225)),
    'medium' => array('resize' => array(640, 480)),
    'large'  => array('resize' => array(1280, 960)),
  );

  protected $upload_error_code;

  protected $tmp_filename;

  ##
  ## validations
  ##

  public function validate() {
  }

  ##
  ## associations
  ##

  public function owner() {
    return User::get($this->owner_id);
  }

  ##
  ## triggers
  ##

  protected function before_validate_on_create() {
    $this->uuid = uuid();
  }

  protected function after_create() {
    # TODO : handle non-uploaded files too
    $dir = App::root . "/public/photos/original/" . $this->uuid_path();
    if(!file_exists($dir))
      mkdir($dir, 0777, true);
    move_uploaded_file($this->tmp_filename, "$dir/{$this->filename}");
  }

  ##
  ## utility methods
  ##

  public function thumbnail($version) {

    require_once App::root . '/vendor/php_thumb/ThumbLib.inc.php';

    $pt = PhpThumb::getInstance();

    #$pt->registerPlugin('GdWatermarkLib','gd');

    $filename = $this->filename;
    $uuid_path = $this->uuid_path();
    $orig_path = App::root . "/public/photos/original/$uuid_path/$filename";

    $thumb = PhpThumbFactory::create($orig_path);

    foreach(self::$versions[$version] as $operation => $options) {
      switch($operation) {
        case 'ar':
          $thumb->adaptiveResize($options[0], $options[1]);
          break;
        case 'resize':
          $thumb->resize($options[0], $options[1]);
          break;
      }
    }

    #$thumb->watermark('watermark.png', $position = 'cc', $padding = 0);

    $thumb_dir = App::root . "/public/photos/$version/$uuid_path";
    mkdir($thumb_dir, 0755, true);
    $thumb->save("$thumb_dir/$filename", 'JPG');

    return $thumb;

  }

  public function url($version = 'original') {
    return "/photos/$version/" . $this->uuid_path() . "/{$this->filename}";
  }

  public function uuid_path() {
    preg_match_all('/..../', str_replace('-', '', $this->uuid), $matches);
    return implode('/', $matches[0]);
  }

  public function set_uploaded_file($key = 'file') {

    $this->filename = $_FILES['photo']['name'][$key];
    $this->content_type = $_FILES['photo']['type'][$key];
    $this->size = $_FILES['photo']['size'][$key];
    $this->tmp_filename = $_FILES['photo']['tmp_name'][$key];

    $error = $_FILES['photo']['error'][$key];
    $this->upload_error_code = $error ? $error : null;

    # TODO : make sure the file was uploaded (is_uploaded_file)

  }

}
