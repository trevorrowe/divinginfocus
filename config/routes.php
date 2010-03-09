<?php

use Pippa\Route;

Route::add('/photos/versions/:version/:id1/:id2/:id3/photo.jpg', array(
  'controller' => 'thumbnails',
  'action' => 'generate',
));

Route::add('/admin', array('controller' => 'admin/users'));

Route::add('/users/:id', array('controller' => 'users', 'action' => 'show'));
Route::add('/users/:id/:action', array('controller' => 'users'));

Route::defaults();
