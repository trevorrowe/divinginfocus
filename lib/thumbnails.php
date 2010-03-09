<?php


# for an iphone web view?
# - iphone_large
# - iphone_thumb

class Thumbnails {

  public static $cfg = array(

    'thumb' => array(
      'quality' => 80,
      'stretch' => true,
      'operations' => array(
        'ar' => array(119,89),
      ),
    ),

    'small' => array(
      'quality' => 80,
      'stretch' => true,
      'operations' => array(
        #'resize' => array(300,225),
        'resize' => array(300,0), # only constrain the width
      ),
    ),

    'medium' => array(
      'quality' => 80,
      'stretch' => true,
      'operations' => array(
        #'resize' => array(640,480),
        'resize' => array(640,0), # only constrain the width
      ),
    ),

    'large' => array(
      'quality' => 100,
      'stretch' => false,
      'operations' => array(
        #'resize' => array(1280,960),
        'resize' => array(1280,0), # only constrain the width
      ),
    ),

    'homepage' => array(
      'quality' => 90,
      'stretch' => false,
      'operations' => array(
        'ar' => array(720,405),
      )
    ),

  );

}
