<?php

function flash() {
  $args = func_get_args();
  switch(func_num_args()) {
    case 0:
      return \Pippa\Flash::$data;
      break;
    case 1:
      return \Pippa\Flash::get($args[0]);
      break;
    case 2:
      \Pippa\Flash::set($args[0], $args[1]);
      break;
    default:
      throw new Exception('invalid args');
  }
}

function flash_now($key, $payload) {
  \Pippa\Flash::set($key, $payload, true);
}

function flash_messages($levels = null) {

  if(is_null($levels)) 
    $levels = array('error', 'warn', 'notice', 'info');

  $flashes = array();
  foreach($levels as $level) {
    if($msg = flash($level)) {
      if(is_array($msg))
        $msg = tag('ul', collect($msg, function($k, $v) { return "<li>$v</li>"; }));
      else
        $msg = tag('p', $msg);
      $flashes[] = tag('div', $msg, array('class' => "$level flash"));
    }
  }

  return empty($flashes) ? 
    null : 
    tag('div', $flashes, array('id' => 'flashes'));

}
