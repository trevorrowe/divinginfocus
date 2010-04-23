<?php 

ini_set('memory_limit', '512M');

ini_set('error_log', '../logs/thumbnailer.log');

$root = realpath(dirname(__FILE__) . '/../..');

require "$root/vendor/php_thumb/ThumbLib.inc.php";
require "$root/lib/thumbnail_options.php";

$version = $_REQUEST['version'];
$id_path = $_REQUEST['id_path'];

# TODO : move most of the logic from here on into lib/thumbnailer.php

if(!isset(Thumbnailer::$versions[$version])) {
  echo "invalid version";
  exit;
}

if(!preg_match('/^\d{3}\/\d{3}\/\d{3}$/', $id_path)) {
  # TODO : show the missing image graphic
  echo "invalid id path";
  exit;
}  

$pt = PhpThumb::getInstance();
#$pt->registerPlugin('GdWatermarkLib','gd');

$src = "$root/public/photos/versions/original/$id_path/photo.jpg";

$thumb = PhpThumbFactory::create($src);
$thumb->setOptions(array(
  'resizeUp' => true,
  'jpegQuality'	=> 80,
  'preserveAlpha' => false,
  'preserveTransparency' => false,
));

## perform image manipulations

foreach(Thumbnailer::$versions[$version] as $operation => $options) {
  switch($operation) {
    case 'ar':
      $thumb->adaptiveResize($options[0], $options[1]);
      break;
    case 'resize':
      $thumb->resize($options[0], $options[1]);
      break;
  }
}

#$thumb->watermark('watermark.png', $position = 'cc', $padding = 0);

#$thumb->createReflection(40, 40, 80, true, '#a4a4a4');

## write the photo to disk

$thumb_dir = "$root/public/photos/versions/$version/$id_path";
mkdir($thumb_dir, 0755, true);

$thumb->save("$thumb_dir/photo.jpg", 'JPG');
$thumb->show();
