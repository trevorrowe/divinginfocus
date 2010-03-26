<?php

use Pippa\Route;

Route::add('/photos/versions/:version/:id1/:id2/:id3/photo.jpg', array(
  'controller' => 'thumbnails',
  'action' => 'generate',
));

Route::add('/users/:username/photos/:id', array(
  'controller' => 'photos',
  'action' => 'show',
));

Route::add('/users/:username/photos/:id/:action', array(
  'controller' => 'photos',
));

Route::add('/users/:username', array(
  'controller' => 'users', 'action' => 'show'
));

Route::add('/users/:username/:action', array('controller' => 'users'));

Route::add('/admin', array('controller' => 'admin/users'));

Route::defaults();
