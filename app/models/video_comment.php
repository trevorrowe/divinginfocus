<?php

class VideoComment extends \Sculpt\Model {

  public static $associations = array(
    
    'video' => array('type' => 'belongs_to'),
    
    'uploader' => array(
      'type' => 'belongs_to',
      'local_key' => 'username',
      'foreign_key' => 'username',
    ),

  );

  public static $scopes = array(
    'recent' => array('order' => 'created_at DESC'),
  );

  public function validate() {
    $this->validate_presence_of('text');
  }

}
