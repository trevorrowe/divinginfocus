<?php

# 0 UPLOAD_ERR_OK - There is no error, the file uploaded with success.
# 1 UPLOAD_ERR_INI_SIZE - The uploaded file exceeds the upload_max_filesize directive in php.ini.
# 2 UPLOAD_ERR_FORM_SIZE - The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
# 3 UPLOAD_ERR_PARTIAL - The uploaded file was only partially uploaded.
# 4 UPLOAD_ERR_NO_FILE - No file was uploaded.
# 5 
# 6 UPLOAD_ERR_NO_TMP_DIR - Missing a temporary folder.
# 7 UPLOAD_ERR_CANT_WRITE - Failed to write file to disk.
# 8 UPLOAD_ERR_EXTENSION

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

  public function form_action($params) {

    if(!isset($_FILES['file'])) {
      $this->redirect('index');
      return;
    }

    $file = $this->build_file();
    $file->set_uploaded_file();
    $file->username = $this->current_user()->username;

    $type = $file->type();

    if($file->save()) {
      $this->flash('notice', "$type uploaded successfully"); 
      $this->redirect(pluralize(underscore($type)), 'show', $file);
    } else {
      $errors = implode(', ', $file->errors->full_messages());
      $this->flash('error', "$type upload failed: $errors");
      $this->redirect('index');
    }
  }

  public function flash_action($params) {

    $batch = UploadBatch::uuid_is($params->batch)->get();

    $file = $this->build_file('Filedata');
    $file->set_uploaded_file('Filedata');
    $file->upload_batch_id = $batch->id;
    $file->username = $batch->username;

    if($file->save()) {
      $url = $file->url('medium');
      $this->render_text("{ 'filename' : 'asdf', 'url' : '$url' }");
    } else {
      $this->status(400); # bad request
      $this->render_text("{ 'error' : 'errors go here' } ");
    }
  }

  protected function build_file($key = 'file') {

    $tmp_filename = $_FILES[$key]['tmp_name'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $content_type = finfo_file($finfo, $tmp_filename);
    finfo_close($finfo);

    if(substr($content_type, 0, 5) == 'video')
      $file = new Video();
    else if(substr($content_type, 0, 5) == 'image')
      $file = new Photo();
    else
      throw new Exception("unhandled file type: $content_type");

    return $file;
  }

}
