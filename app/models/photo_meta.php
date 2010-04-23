<?php

class PhotoMeta extends \Sculpt\Model {

  public static $table_name = 'photo_meta';

  public static $associations = array(
    
    'photo' => array('type' => 'belongs_to'),

  );

}
