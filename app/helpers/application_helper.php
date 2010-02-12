<?php

class ApplicationHelper extends \Pippa\Helper {

  # returns true if the user is logged in
  public function logged_in() {
    return isset(App::$session['user_id']);
  }

  public function user_is_sudoed() {
    return App::$session->sudo_id ? true : false;
  }

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

  public function icon_link($class_name, $label, $url, $opts = array()) {
    $this->append_class_name($opts, 'icon');
    $this->append_class_name($opts, $class_name);
    return $this->link_to($label, $url, $opts);
  }

  public function icon_only_link($class_name, $label, $url, $opts = array()) {
    $this->append_class_name($opts, 'icon_only');
    $this->append_class_name($opts, $class_name);
    $opts['title'] = $label;
    return $this->link_to($label, $url, $opts);
  }

}
