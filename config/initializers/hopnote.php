<?php

use \Pippa\App;

require(App::root . '/vendor/hopnote/Hopnote.php');

Hopnote::register_handlers('72f3e257342bd683d986a4ef5f70be84', array(
  'environment' => App::env,
  'deployed' => App::env == 'production',
  'fatals' => TRUE,
  'root' => App::root,
  'errors' => E_ALL | E_STRICT,
  'fivehundred' => App::root . '/public/500.html',
));