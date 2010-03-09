<?php

class Photo extends \Sculpt\Model {

  protected $upload_error_code;

  protected $exif;

  protected $tmp_filename;

  ##
  ## validations
  ##

  public function validate() {

    #$this->validate_inclusion_of('content_type', array(
    #  # TODO : support other image formats
    #  'in' => array('image/jpeg'),
    #));

    $this->validate_length_of('title', array(
      'maximum' => 100,
      'allow_null' => true,
    ));

    $this->validate_length_of('caption', array(
      'maximum' => '2000',
      'allow_null' => true,
    ));

    $this->validate_format_of('content_type', array(
      'regex' => '/^image\/jpeg$/'
    ));
    
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

  protected function after_create() {
    $dir = App::root . '/public/photos/versions/original/' . $this->id_path();
    if(!file_exists($dir))
      mkdir($dir, 0777, true);
    move_uploaded_file($this->tmp_filename, "$dir/photo.jpg");
  }

  # After the photo is destroyed from the db we need to clean up the original
  # and all of its generated versions from disk.  We are also going to take
  # care to prune empty directories left behind.
  protected function after_destroy() {
    $glob_path = App::root . "/public/photos/versions/**/" . $this->id_path();
    do {
      exec("rm -rf $glob_path");
      $glob_path = dirname($glob_path);
      $orig_path = str_replace('**', 'original', $glob_path);
      if(basename($orig_path) == 'original')
        break;
    } while(($files = scandir($orig_path)) && count($files) < 3);
  }

  ##
  ## utility methods
  ##

  public function _title() {
    if($title = $this->_get('title'))
      return $title;
    else
      return preg_replace('/\..+$/', '', $this->filename);
  }

  public function alt() {
    if($caption = $this->_get('caption'))
      return $caption;
    else
      return $this->title;
  }

  public function url($version = 'original') {
    return "/photos/versions/$version/" . $this->id_path() . '/photo.jpg';
  }

  ##
  ## exif data functions
  ##

  public function exif_data() {
    if(is_null($this->exif))
      $this->exif = exif_read_data(App::root . '/public' . $this->url());
    return $this->exif;
  }

  ##
  ## pathing functions
  ##

  protected function id_path() {
    return implode('/', str_split(str_pad($this->id, 9, '0', STR_PAD_LEFT), 3));
  }

  public function set_uploaded_file($key = 'file') {

    $this->filename = $_FILES[$key]['name'];
    #$this->content_type = $_FILES[$key]['type'];
    $this->size = $_FILES[$key]['size'];
    $this->tmp_filename = $_FILES[$key]['tmp_name'];

    $error = $_FILES[$key]['error'];
    $this->upload_error_code = $error ? $error : null;

    # determine the real content type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $this->content_type = finfo_file($finfo, $this->tmp_filename);
    finfo_close($finfo);

    # TODO : make sure the file was uploaded (is_uploaded_file)

  }

}
