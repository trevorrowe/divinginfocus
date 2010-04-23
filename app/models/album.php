<?php

class Album extends \Sculpt\Model {

  public static $associations = array(

    'owner' => array(
      'type' => 'belongs_to',
      'class' => 'User',
      'local_key' => 'username',
      'foreign_key' => 'username'),

    # has many media

  );

}
