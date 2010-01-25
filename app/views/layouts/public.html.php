<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo isset($title) ? "Diving in Focus : $title" : 'Diving in Focus' ?></title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <?php echo css_tag('layouts/public') ?>
  <?php echo css_tag("controllers/{$params->controller}") ?>
</head>
<body>
  <div id='cntl' class='<?php echo $params->controller ?>'>
    <div id='actn' class='<?php echo $params->action ?> content'>
      <?php echo flash_messages() ?>
      <?php echo $_content ?>
    </div>
  </div>
  <div id='header'>
    <div class="content">
      <h2>Diving in Focus</h2>
    </div>
  </div>
  <div id='footer'>
    <div class="content">
    </div>
  </div>
</body>
</html>
