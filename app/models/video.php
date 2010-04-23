<?php

class Video extends MediaFile {

  public static $associations = array(

    'uploader' => array(
      'type' => 'belongs_to',
      'class' => 'User',
      'local_key' => 'username',
      'foreign_key' => 'username'),

    'upload_batch' => array(
      'type' => 'belongs_to'),

    'favored_by' => array(
      'type' => 'has_and_belongs_to_many',
      'join_table' => 'video_favorites',
      'class' => 'User',
      'target_col' => 'username',
      'target_key' => 'username'),

    'comments' => array(
      'type' => 'has_many',
      'class' => 'VideoComment'),

  );

  ##
  ## utility methods
  ##

  public function url() {
    return $this->orig_url();
  }

}
