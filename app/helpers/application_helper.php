<?php

class ApplicationHelper extends \Pippa\Helper {

  # return true if there is a user logged into the current session
  public function logged_in() {
    return isset(App::$session['user_id']);
  }

  # returns true if the current user is actually another user sudoing
  public function user_is_sudoed() {
    return App::$session->sudo_id ? true : false;
  }

  # returns true if the current user has privileges to sudo
  public function user_can_sudo() {
    return (
      ($this->logged_in()) && 
      ($this->current_user()->admin || App::$session->sudo_id));
  }

  # returns the current user object (if logged in), null otherwise
  public function current_user() {
    if($this->logged_in())
      if(is_null($this->current_user))
        $this->current_user = User::get(App::$session->user_id);
    return $this->current_user;
  }

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

  # return the url to the photo html page
  public function photo_url($photo) {
    return url('photos', 'show', $photo);
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
    $opts['title'] = $photo->title;
    $opts['alt'] = $photo->alt();
    return $this->img_tag($photo->url($version), $opts);
  }

  # return a link to a photo page wrapping an img tag
  public function photo_link($photo, $version, $opts = array()) {
    $img = $this->photo_tag($photo, $version);
    $opts['title'] = $photo->filename;
    return $this->link_to($img, $this->photo_url($photo), $opts);
  }

  public function add_head_tag($tag) {
    $tags = $this->head_tags ? $this->head_tags : '';
    $tags .= $tag;
    $this->head_tags = $tags;
  }

  public function add_js($asset) {
    $tags = $this->js_tags ? $this->js_tags : '';
    $tags .= $this->js_tag($asset);
    $this->js_tags = $tags;
  }

}
