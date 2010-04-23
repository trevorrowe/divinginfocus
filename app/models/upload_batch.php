<?php

class UploadBatch extends \Sculpt\Model {

  public static $associations = array(

    'user' => array(
      'type' => 'belongs_to',
      'local_key' => 'username',
      'foreign_key' => 'username',
    ),

    'photos' => array('type' => 'has_many'),

  );

  public function validate() {
    $this->validate_as_uuid('uuid');
  }

  public function init() {
    $this->uuid = uuid();    
  }

}
