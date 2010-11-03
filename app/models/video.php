<?php

class Video extends MediaFile {

  ##
  ## validations
  ##

  public function validate() {

    parent::validate();

    $this->validate_format_of('content_type', array(
      'regex' => '/^video/',
    ));
    
  }

  ##
  ## utility methods
  ##

  public function url() {
    return $this->orig_url();
  }

}
