<?php

class ApplicationHelper extends \Pippa\Helper {

  # returns true if the user is logged in
  public function logged_in() {
    return isset(App::$session['user_id']);
  }

  # returns the current user object (if logged in), null otherwise
  public function current_user() {
    if($this->logged_in()) {
      $user_id = App::$session['user_id'];
      return User::get($user_id);
    }
    return null;
  }

  # returns a h1 title tag and sets the title for use in the layout
  public function title($title) {
    $this->title = $title;
    return $this->tag('h1', $title);
  }

}
