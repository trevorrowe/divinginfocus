<!DOCTYPE html>
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

<header>
  <?php echo $this->crumbtrail() ?>
  <?php $this->render('/shared/user_links') ?>
  <nav id='menu'>
    <ul>
      <li><?php echo $this->link_to('Upload', '/upload') ?></li>
      <li><?php echo $this->link_to('Photos', '/photos') ?></li>
      <li><?php echo $this->link_to('Videos', '/videos') ?></li>
      <li><?php echo $this->link_to('Dive Reports', '/users') ?></li>
      <li><?php echo $this->link_to('Users', '/users') ?></li>
      <li class='last'>
      <?php if($this->logged_in()): ?>
        <?php echo $this->link_to('Home', '/home') ?>
      <?php else: ?>
        <?php echo $this->link_to('Login / Register', '/login') ?>
      <?php endif ?>
      </li>
    </ul>
  </nav>
</header>

<footer>
  &copy; <?php echo date('Y') ?> DivingInFocus.com
</footer>

<div id='scripts'>
  <?php echo $this->js_tag('jquery-1.4.1.min') ?>
  <?php echo $this->js_tag('jquery.cookie') ?>
  <?php echo $this->js_tag('public') ?>
  <?php echo $this->js_tags ?>
</div>

</body>
</html>
