<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo isset($title) ? "$title - " : '' ?>DivingInFocus.com Admin</title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <?php echo css_tag('layouts/admin') ?>
  <?php echo js_tag('jquery-1.3.2.min') ?>
</head>
<body>
<div id='cntl' class='<?php echo $params['controller'] ?>'>
<div id='actn' class='<?php echo $params['action'] ?>'>

<div id='content'>
  <div id='content_box'>
    <?php echo flash_messages() ?>
    <?php echo $_content ?>
  </div><!-- end content_box -->
  <div id='footer'>
    <p>&copy; 2009 DivingInFocus.com</p>
  </div><!-- end footer -->
</div><!-- end content -->

<div id='sidebar'>
  <?php $this->render('sidebar') ?>
</div><!-- end sidebar -->

<ul id='menu'>
  <li><?php echo link_to('Users', array('controller' => 'admin/users')) ?></li>
</ul>

</div><!-- end actn -->
</div><!-- end cntl -->
</body>
</html>
