<?php

class ApplicationHelper extends \Pippa\Helper {

  ##
  ## authorization & authentication
  ##

  # return true if there is a user logged into the current session
  public function logged_in() {
    return isset(App::$session['username']);
  }

  # returns true if the current user is actually another user sudoing
  public function user_is_sudoed() {
    return App::$session->sudo_username ? true : false;
  }

  # returns true if the current user has privileges to sudo
  public function user_can_sudo() {
    return (
      ($this->logged_in()) && 
      ($this->current_user()->admin || App::$session->sudo_username));
  }

  # returns the current user object (if logged in), null otherwise
  public function current_user() {
    if($this->logged_in())
      if(is_null($this->current_user))
        $this->current_user = User::username_is(App::$session->username)->get();
    return $this->current_user;
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
  ## photo links & tags
  ##

  # return the url to the photo html page
  public function photo_url($photo, $action = 'show') {
    return url(array(
      'controller' => 'photos',
      'action' => $action,
      'username' => $photo->username,
      'id' => $photo,
    ));
  }

  # returns an image tag tailored for the photo
  public function photo_tag($photo, $version, $opts = array()) {
    $cfg = Thumbnails::$cfg;
    if(isset($cfg[$version]['operations']['ar'])) {
      $opts['width'] = $cfg[$version]['operations']['ar'][0];
      $opts['height'] = $cfg[$version]['operations']['ar'][1];
    } else {
      # TODO : caclulate height based on the $photo->height
      $opts['width'] = $cfg[$version]['operations']['resize'][0];
    }
    $opts['title'] = "{$photo->title} by {$photo->username}";
    $opts['alt'] = $photo->alt();
    return $this->img_tag($photo->url($version), $opts);
  }

  # return a link to a photo page wrapping an img tag
  public function photo_link($photo, $version, $opts = array()) {
    $img = $this->photo_tag($photo, $version);
    $this->append_class_name($opts, 'photo');
    return $this->link_to($img, $this->photo_url($photo), $opts);
  }

  public function photo_quilt($collection, $more_url, $opts = array()) {
    $parts = array();
    foreach($collection as $photo)  
      $parts[] = $this->photo_link($photo, 'thumb');
    $parts[] = $this->link_to('More &raquo;', $more_url);
    return $this->tag('div', $parts, array('class' => 'photo_quilt'));
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
      $url = $this->photo_url($photo, 'unfavorite');
      $class = 'remove';
    } else {
      $iurl = '/images/photos/unfavored.png';
      $text = 'Click to add to favorites';
      $img = $this->img_tag($iurl, array('alt' => $text));
      $url = $this->photo_url($photo, 'favorite');
      $class = 'add';
    }
    return $this->link_to($img, $url, array(
      'class' => "$class favorite",
      'title' => $text,
    ));
  }

}
