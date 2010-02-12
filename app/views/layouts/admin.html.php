<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo isset($title) ? "$title - " : '' ?>dnfoc.us Admin Interface</title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <?php echo $this->css_tag('admin') ?>
  <?php echo $this->js_tag('jquery-1.3.2.min') ?>
</head>
<body>
<div id='cntl' class='<?php echo $params['controller'] ?>'>
<div id='actn' class='<?php echo $params['action'] ?>'>

<div id='content'>
  <div id='content_box'>
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
  </div><!-- end content_box -->
  <div id='footer'>
    <p>&copy; 2009 DivingInFocus.com</p>
  </div><!-- end footer -->
</div><!-- end content -->

<div id='sidebar'>
  <?php $this->render('sidebar') ?>
</div><!-- end sidebar -->

<div id="header">
  <h2>Diving In Focus &mdash; Admin Interface</h2>
  <?php $this->render('/shared/user_links') ?>
  <?php if($this->current_user()->admin): ?>
    <ul id='menu'>
      <li><?php echo $this->link_to('Users', array('controller' => 'admin/users')) ?></li>
    </ul><!-- end menu -->
  <?php endif; ?>
</div><!-- end header -->

</div><!-- end actn -->
</div><!-- end cntl -->
</body>
</html>
