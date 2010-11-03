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
  ## shared actions
  ##

  public function show_action($params) {
    $this->comments = $this->media->comments->recent->paginate($params->page);
    $this->comment = $this->media->comments->build($params["{$this->lc_type}_comment"]);
  }

  public function comment_action($params) {
    $this->show_action($params);
    $this->comment->username = $this->current_user()->username;
    if($this->comment->save()) {
      $this->flash('notice', 'Your comment has been added.');
      $this->redirect($this->media_url($this->photo));
    } else {
      $this->flash_now('error', 'Unable to add your comment, see errors below.');
      $this->render('show');
    }
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

      $this->add_crumb($media->title, $this->media_url($media));

      $lc_type = strtolower($class);

      $this->type = $class;
      $this->media = $media;
      $this->$lc_type = $media;

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
