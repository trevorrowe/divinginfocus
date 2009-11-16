<?php

function path($params) {
  echo 'global params';
}

function url($params) { 
  echo 'global url';
}

function debug($obj) {
  echo '<pre>';
  #var_dump($obj, true);
  print_r($obj);
  echo "</pre>\n";
}
