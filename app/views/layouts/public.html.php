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
    </div>
  </div>
  <div id='header'>
    <div class="content">
      <h2>Diving in Focus</h2>
      <div id="login_logout">
        <?php if($this->logged_in()): ?>
        <?php echo $this->link_to('Logout', url('logout', 'index', null)) ?>
        <?php else: ?>
        <?php echo $this->link_to('Login', url('login', 'index', null)) ?>
        <?php endif; ?>
      </div>
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
