<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo isset($title) ? "Diving in Focus : $title" : 'Diving in Focus' ?></title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <?php echo $this->css_tag('public') ?>
  <?php echo $this->css_tag($params->controller) ?>
</head>
<body>
  <div id='cntl' class='<?php echo $params->controller ?>'>
    <div id='actn' class='<?php echo $params->action ?> content'>
      <?php echo $this->flash_messages() ?>
      <?php echo $_content ?>
      <dl>
        <dt>Params</dt>
        <dd><?php echo $params ?></dd>
        <dt>Session</dt>
        <dd><?php echo App::$session ?></dd>
        <dt>Remember Me Cookie</dt>
        <dd><?php echo RememberMeCookie::get() ?></dd>
      </dl>
    </div>
  </div>
  <div id='header'>
    <div class="content">
      <h2>Diving in Focus</h2>
      <?php $this->render('/shared/user_links') ?>
    </div>
  </div>
  <div id='footer'>
    <div class="content">
    </div>
  </div>
  <div id='scripts'>
    <?php echo $this->js_tag('jquery-1.4.1.min') ?>
    <?php echo $this->js_tag('jquery.cookie') ?>
    <?php echo $this->js_tag('public') ?>
  </div>
</body>
</html>
