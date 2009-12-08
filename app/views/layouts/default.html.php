<!DOCTYPE html>
<html>
<head>
  <title><?php echo isset($title) ? "$title : Diving in Focus" : "Diving in Focus" ?></title>
  <meta charset="UTF-8">
</head>
<body>
  <div id="cntl" class="<?php echo $params['controller']; ?>">
    <div id="actn" class="<?php echo $params['action']; ?>">
      <div id="content">
        <?php echo $content ?>
      </div>
      <div id="header">
      </div>
      <div id="footer">
      </div>
    </div>
  </div>
</body>
</html>
