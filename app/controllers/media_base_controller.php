<?php

class MediaBaseController extends PublicBaseController {

  public function init() {
    parent::init();
    $this->before_filter('require_user', array('only' => array(
      'comment', 
      'favorite', 
      'unfavorite', 
      'destroy',
    )));
    $this->before_filter('add_crumbs');
    $this->before_filter('load_media');
  }

  ##
  ## filters
  ##

  public static function media_class() {
    return substr(get_called_class(), 0, 5);
  }

  public function load_media_filter($params) {
    if($params->id) {

      $class = static::media_class();
      $media = $class::get($params->id);

      $assoc = strtolower($class);
      $show_url = $this->media_url($media);

      $this->add_crumb($media->title, $show_url);
      $this->$assoc = $media;


    }
  }

  public function add_crumbs_filter($params) {

    $class = static::media_class();

    if($params->username) {
      $this->add_crumb('Users', '/users');
      $this->add_crumb($params->username, $this->user_path($params->username));
      $this->add_crumb("{$class}s", 
        $this->user_path($params->username, strtolower($class) .  's'));
    }
  }

}
