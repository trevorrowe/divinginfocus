<?php

class ThumbnailsController extends ApplicationController {

  public static $layout = false;

  public function generate_action($params, $request) {

    ini_set('memory_limit', '512M');

    require App::root . '/vendor/php_thumb/ThumbLib.inc.php';

    $id_path = "{$params->id1}/{$params->id2}/{$params->id3}/{$params->id4}";

    # TODO : if photo not found, redirect to 404 with missing image graphic
    $photo = Photo::get((int) str_replace('/', '', $id_path));

    $version = $params->version;

    if(!isset(Thumbnails::$cfg[$version])) {
      $this->render_error_page('404');
      return;
    }

    $pt = PhpThumb::getInstance();
    #$pt->registerPlugin('GdWatermarkLib','gd');

    $thumb = PhpThumbFactory::create($photo->orig_disk_path());
    $thumb->setOptions(array(
      'resizeUp' => Thumbnails::$cfg[$version]['stretch'],
      'jpegQuality'	=> Thumbnails::$cfg[$version]['quality'],
      'preserveAlpha' => false,
      'preserveTransparency' => false,
    ));

    foreach(Thumbnails::$cfg[$version]['operations'] as $operation => $args) {
      switch($operation) {
        case 'ar':
          $thumb->adaptiveResize($args[0], $args[1]);
          break;
        case 'resize':
          $thumb->resize($args[0], $args[1]);
          break;
      }
    }

    #$thumb->watermark('watermark.png', $position = 'cc', $padding = 0);
    #$thumb->createReflection(40, 40, 80, true, '#a4a4a4');

    $thumb_dir = App::root . "/public/photos/versions/$version/$id_path";
    if(!file_exists($thumb_dir))
      mkdir($thumb_dir, 0755, true);

    $thumb->save("$thumb_dir/photo.jpg", 'JPG');
    $thumb->show();
    $this->render(false);

  }

}
