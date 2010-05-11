<?php

namespace Pippa\Helpers;

class Flashes extends \Pippa\Helper {

  public function flash() {
    $args = func_get_args();
    switch(count($args)) {
      case 1:
        return \App::$flash[$args[0]];
        break;
      case 2:
        \App::$flash[$args[0]] = $args[1];
        break;
      default:
        throw new Exception('invalid args');
    }
  }

  public function flash_now($key, $payload) {
    \App::$flash->$key = $payload;
    \App::$flash->set_to_expire($key);
  }

  public function flash_messages($levels = null) {

    if(is_null($levels)) 
      $levels = array('error', 'warn', 'notice', 'info');

    $flashes = array();
    foreach($levels as $level) {
      if($msg = $this->flash($level)) {
        if(is_array($msg))
          $msg = $this->tag('ul', collect($msg, function($k, $v) { return "<li>$v</li>"; }));
        else
          $msg = $this->tag('p', $msg);
        $flashes[] = $this->tag('div', $msg, array('class' => "$level flash"));
      }
    }

    return empty($flashes) ? 
      null : 
      $this->tag('div', $flashes, array('id' => 'flashes'));
  }

}
