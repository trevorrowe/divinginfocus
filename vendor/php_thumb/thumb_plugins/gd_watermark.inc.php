<?php

class GdWatermarkLib {

  /**
   * Instance of GdThumb passed to this class
   *
   * @var GdThumb
   */

  protected $parentInstance;
  protected $currentDimensions;
  protected $workingImage;
  protected $newImage;
  protected $options;

  public function watermark($mask, $position = 'cc', $padding = 0, &$that) {

    $this->mask = $mask;
    $this->position = $position;
    $this->padding = $padding;

    // bring stuff from the parent class into this class...
    $this->parentInstance = $that;
    $this->currentDimensions = $this->parentInstance->getCurrentDimensions();
    $this->workingImage = $this->parentInstance->getWorkingImage();
    $this->newImage = $this->parentInstance->getOldImage();
    $this->options = $this->parentInstance->getOptions();

    $canvas_width = $this->currentDimensions['width'];
    $canvas_height = $this->currentDimensions['height'];

/*
    if ($canvas_width <= 200 || $canvas_height <= 200) {
      return $that;
    }
*/

    list($stamp_width, $stamp_height, $stamp_type, $stamp_attr) = getimagesize($mask);

    switch ($stamp_type) {
      case 1:
        $stamp = imagecreatefromgif($mask);
        break;
      case 2:
        @ini_set('gd.jpeg_ignore_warning', 1);
        $stamp = imagecreatefromjpeg($mask);
        break;
      case 3:
        $stamp = imagecreatefrompng($mask);
        break;
    }

    imagealphablending($this->workingImage, true);

    if($stamp_width > $canvas_width || $stamp_height > $canvas_height) {
      // some simple resize math
      //$water_resize_factor = round($canvas_width / $stamp_width);
      $water_resize_factor = 0.5;
      $new_mask_width = $stamp_width * $water_resize_factor;
      $new_mask_height = $stamp_height * $water_resize_factor;
      $padding = $padding * $water_resize_factor;
      // the new watermark creation takes place starting from here
      $new_mask_image = imagecreatetruecolor($new_mask_width , $new_mask_height);
      // imagealphablending is important in order to keep, our png image (the watewrmark) transparent
      imagealphablending($new_mask_image , false);
      imagecopyresampled(
          $new_mask_image , $stamp, 0, 0, 0, 0,
          $new_mask_width, $new_mask_height,
          $stamp_width, $stamp_height
          );
      // assign the new values to the old variables
      $stamp_width = $new_mask_width;
      $stamp_height = $new_mask_height;
      $stamp = $new_mask_image;
    }

    switch($position) {
      case 'cc':
        // Center
        $start_width = round(($canvas_width - $stamp_width) / 2);
        $start_height = round(($canvas_height - $stamp_height) / 2);
        break;
      case 'lt':
        // Left Top
        $start_width = $padding;
        $start_height = $padding;
        break;
      case 'rt':
        // Right Top
        $start_width = $canvas_width - $padding - $stamp_width;
        $start_height = $padding;
        break;
      case 'lb':
        // Left Bottom
        $start_width = $padding;
        $start_height = $canvas_height - $padding - $stamp_height;
        break;
      case 'rb':
        // Right Bottom
        $start_width = $canvas_width - $padding - $stamp_width;
        $start_height = $canvas_height - $padding - $stamp_height;
        break;
      case 'cb':
        // Center Bottom
        $start_width = round(($canvas_width - $stamp_width) / 2);
        $start_height = $canvas_height - $padding - $stamp_height;
        break;
    }

    imagecopy($this->workingImage, $stamp, $start_width, $start_height, 0, 0, $stamp_width, $stamp_height );

    imagedestroy($stamp);

    return $that;
  }
}
