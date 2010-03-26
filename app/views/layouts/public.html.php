<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo isset($title) ? "$title : Diving in Focus" : 'Diving in Focus' ?></title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <link rel="SHORTCUT ICON" href="/favicon.ico"/>
  <?php echo $this->css_tag('public') ?>
  <?php echo $this->css_tag($params->controller) ?>
  <?php echo $this->head_tags ?>
</head>
<body>

<div id='cntl' class='<?php echo $params->controller ?>'>
  <div id='actn' class='<?php echo $params->action ?> content'>
    <?php echo $this->flash_messages() ?>
    <div id='content'>
      <?php echo $_content ?>
    </div>
  </div>
</div>

<div id='header'>
  <?php echo $this->crumbtrail() ?>
  <?php $this->render('/shared/user_links') ?>
</div>

<div id='menu'>
  <ul>
    <li><?php echo $this->link_to('Upload', '/upload') ?></li>
    <li><?php echo $this->link_to('Photos', '/photos') ?></li>
    <li><?php echo $this->link_to('Users', '/users') ?></li>
    <li class='last'>
    <?php if($this->logged_in()): ?>
      <?php echo $this->link_to('Me', '/users') ?>
    <?php else: ?>
      <?php echo $this->link_to('Login / Register', '/login') ?>
    <?php endif ?>
    </li>
  </ul>
</div>

<div id='footer'>
  &copy; <?php echo date('Y') ?> DivingInFocus.com
</div>

<div id='scripts'>
  <?php echo $this->js_tag('jquery-1.4.1.min') ?>
  <?php echo $this->js_tag('jquery.cookie') ?>
  <?php echo $this->js_tag('public') ?>
  <?php echo $this->js_tags ?>
</div>

</body>
</html>
