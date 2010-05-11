<!DOCTYPE html>
<html>
<head>
  <title><?php echo isset($title) ? "$title : " : '' ?>DivingInFocus.com Admin Interface</title>
  <meta content='text/html;charset=UTF-8' http-equiv='content-type' />
  <link rel="SHORTCUT ICON" href="/favicon.ico"/>
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
  </div><!-- end #content_box -->
  <footer>
    <p>&copy; <?php echo date('Y') ?> DivingInFocus.com</p>
  </footer>
</div><!-- end #content -->

<div id='sidebar'>
  <?php $this->render('sidebar') ?>
</div><!-- end #sidebar -->

<header>
  <h2>
    <?php echo $this->link_to('Diving In Focus', '/') ?>
    &mdash; Admin Interface
  </h2>
  <?php $this->render('/shared/user_links') ?>
  <?php if($this->current_user()->admin): ?>
    <nav id='menu'>
      <ul>
        <li><?php echo $this->link_to('Users', array('controller' => 'admin/users')) ?></li>
      </ul
    </nav>
  <?php endif; ?>
</header>

</div><!-- end #actn -->
</div><!-- end #cntl -->

<div id='scripts'>
  <?php echo $this->js_tag('jquery-1.4.1.min') ?>
  <?php echo $this->js_tag('admin') ?>
  <?php echo $this->js_tags ?>
</div> <!-- end #scripts -->

</body>
</html>
