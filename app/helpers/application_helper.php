<?php

class ApplicationHelper extends \Pippa\Helper {

  ##
  ## authorization & authentication
  ##

  # Returns true if there is a user logged into the current session
  public function logged_in() {
    return(is_null($this->current_user()) ? false : true);
  }

  # Returns true if the current user is actually another user sudoing
  public function user_is_sudoed() {
    return App::$session->sudoer_username ? true : false;
  }

  public function sudo_path($user_or_username) {

    $username = is_object($user_or_username) ? 
      $user_or_username->username :
      $user_or_username;

    return url(array(
      'controller' => 'sudo',
      'action' => 'login_as',
      'username' => $username,
    ));
  }

  # Returns true if the current user has privileges to sudo
  public function user_can_sudo() {
    return (
      $this->logged_in() && 
      $this->current_user()->admin || 
      $this->user_is_sudoed()
    );
  }

  # Returns the current user object if logged in, null otherwise
  public function current_user() {
    # We only want to find the current user once.  We will cache it in
    # the local "_current_user"
    if(isset(App::$session['username']) and is_null($this->_current_user)) {
      $user = User::username_is(App::$session->username)->first();
      if(is_null($user)) {
        # the session contained a username to a user that no longer exists
        # by that name in the database, so lets remove the username
        # from the session
        unset(App::$session['username']);
      }
      $this->_current_user = $user;
    }
    return $this->_current_user;
  }

  ##
  ## titles & links
  ##

  # returns a h1 title tag and sets the title for use in the layout
  public function title($title) {
    $this->title = $title;
    return $this->tag('h1', $title);
  }

  # return a link with an icon in front
  public function icon_link($class_name, $label, $url, $opts = array()) {
    $this->append_class_name($opts, 'icon');
    $this->append_class_name($opts, $class_name);
    return $this->link_to($label, $url, $opts);
  }

  # returns a icon only link
  public function icon_only_link($class_name, $label, $url, $opts = array()) {
    $this->append_class_name($opts, 'icon_only');
    $this->append_class_name($opts, $class_name);
    $opts['title'] = $label;
    return $this->link_to($label, $url, $opts);
  }

  ##
  ## media helpers
  ##

  public function media_url($media, $opts_or_action = array()) {

    $opts = $opts_or_action;

    if(is_string($opts))
      $opts = array('action' => $opts);
    else if(!isset($opts['action']))
      $opts['action'] = 'show';

    return url(array_merge(array(
      'controller' => $media::plural_type(),
      'username' => $media->username,
      'id' => $media,
    ), $opts));

  }

  public function media_link($media, $content, $opts = array()) {
    $this->append_class_name($opts, $media::type());
    return $this->link_to($content, $this->media_url($media), $opts);
  }

  public function linked_media($media, $size = 'thumb', $opts = array()) {
    $img = $this->media_thumb($media, $size);
    return $this->media_link($media, $img, $opts);
  }

  public function media_thumb($media, $size, $opts = array()) {
    switch($media::type()) {
      case 'photo':
        return $this->photo_thumb($media, $size, $opts);
      case 'video':
        return $this->video_thumb($media, $size, $opts);
      case 'album':
        return $this->album_thumb($media, $size, $opts);
    }
  }

  public function video_thumb($video, $size, $opts = array()) {
    $this->append_class_name($opts, 'video');
    $this->append_class_name($opts, $size);
    $this->append_class_name($opts, 'thumb');
    return $this->img_tag('/images/videos/play_arrow.png', $opts);
  }

  public function photo_thumb($photo, $size, $opts = array()) {
    $cfg = Thumbnails::$cfg;
    if(isset($cfg[$size]['operations']['ar'])) {
      $opts['width'] = $cfg[$size]['operations']['ar'][0];
      $opts['height'] = $cfg[$size]['operations']['ar'][1];
    } else {
      # TODO : caclulate height based on the $photo->height
      $opts['width'] = $cfg[$size]['operations']['resize'][0];
    }
    $opts['title'] = $photo->html_title();
    $opts['alt'] = $photo->html_alt();
    $this->append_class_name($opts, 'photo');
    $this->append_class_name($opts, $size);
    $this->append_class_name($opts, 'thumb');
    return $this->img_tag($photo->url($size), $opts);
  }

  public function album_thumb($album, $size, $opts = array()) {
    $this->append_class_name($opts, 'ablum');
    $this->append_class_name($opts, $size);
    $this->append_class_name($opts, 'thumb');
    return $this->img_tag('/images/videos/play_arrow.png', $opts);
  }

  public function quilt($collection, $more_url = null, $opts = array()) {

    $parts = array();
    foreach($collection as $media)  
      $parts[] = $this->linked_media($media, 'thumb');

    if($collection->pages > 1 && $more_url)
      $parts[] = $this->link_to('More &raquo;', $more_url);

    $this->append_class_name($opts, 'quilt');
    return $this->tag('div', $parts, $opts);

  }

  public function user_path($user_or_username, $action = 'show') {
    return url(array(
      'controller' => 'users',
      'action' => $action,
      'username' => $this->username($user_or_username),
    ));
  }

  public function user_link($user_or_username) {
    $username = $this->username($user_or_username);
    return $this->link_to($username, $this->user_path($username));
  }

  private function username($user_or_username) {
    return $username = is_a($user_or_username, 'User') ? 
      $user_or_username->username : 
      $user_or_username;
  }

  ##
  ## head tags
  ##

  public function add_head_tag($tag) {
    if(is_null($this->head_tags))
      $this->head_tags = '';
    $this->head_tags .= $this->link_to($label, $url);
  }

  public function add_js($asset) {
    if(is_null($this->js_tags))
      $this->js_tags = '';
    $this->js_tags .= $this->js_tag($asset);
  }

  ##
  ## bread crumbs
  ##

  public function crumbtrail() {
    if(empty($this->crumbs))
      return null;
    return $this->tag('div', $this->crumbs, array('id' => 'crumbtrail'));
  }

  public function add_crumb($label, $url = '') {

    if($url == '')
      $url = $this->request->uri;

    $link = $this->link_to($label, $url);

    if(is_null($this->crumbs)) {
      $this->crumbs = array();
      $prefix = '';
      $link = $this->tag('h2', $link);
    } else {
      $link = "&raquo; $link";
    }
    $crumb = $this->tag('div', $link, array('class' => 'crumb'));
    array_push($this->crumbs, $crumb);
  }

  ##
  ## ajax checkbox toggle
  ##

  public function ajax_toggle($obj, $attr) {
    $opts = array();
    $opts['class'] = 'toggle';
    $opts['data-url'] = url("toggle_$attr", $obj);
    return $this->checkbox_tag(null, $obj->$attr, $opts);
  }

  ##
  ## favorite photo link
  ##

  public function favorite_photo_link($photo) {
    $user = $this->current_user();
    if(!$user)
      return '';
    if($user->favorite_photos->contains($this->photo)) {
      $iurl = '/images/photos/favored.png';
      $text = 'Click to remove from favorites';
      $img = $this->img_tag($iurl, array('alt' => $text));
      $url = $this->media_url($photo, 'unfavorite');
      $class = 'remove';
    } else {
      $iurl = '/images/photos/unfavored.png';
      $text = 'Click to add to favorites';
      $img = $this->img_tag($iurl, array('alt' => $text));
      $url = $this->media_url($photo, 'favorite');
      $class = 'add';
    }
    return $this->link_to($img, $url, array(
      'class' => "$class favorite",
      'title' => $text,
    ));
  }

}
