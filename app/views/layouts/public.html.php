<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo isset($title) ? "$title : Diving in Focus" : 'Diving in Focus' ?></title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <?php echo css_tag('layouts/application') ?>
  <?php echo js_tag('prototype') ?>
</head>
<body>
  <div id='cntl' class='<?php echo $params['controller'] ?>'>
    <div id='actn' class='<?php echo $params['action'] ?>'>
      <?php echo flash_messages() ?>
      <div id='content'>
        <h1>Public Layout</h1>
        <?php echo $content ?>
      </div>
      <div id='header'>
      </div>
      <div id='footer'>
      </div>
    </div>
  </div>
</body>
</html>
