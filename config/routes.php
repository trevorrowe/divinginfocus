<?php

use Pippa\Route;

Route::add('/photos/versions/:version/:id1/:id2/:id3/:id4/photo.jpg', array(
  'controller' => 'thumbnails',
  'action' => 'generate',
));

foreach(array('photos', 'videos') as $media) {

  Route::add("/users/:username/$media/:id", array(
    'controller' => $media,
    'action' => 'show',
  ));

  Route::add("/users/:username/$media/:id/:action", array(
    'controller' => $media,
  ));

}

Route::add('/users/:username', array(
  'controller' => 'users', 'action' => 'show'
));

Route::add('/users/:username/:action', array('controller' => 'users'));

Route::add('/admin', array('controller' => 'admin/users'));

Route::defaults();
