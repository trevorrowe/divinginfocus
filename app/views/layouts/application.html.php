<!DOCTYPE html>
<html>
<head>
  <title><?php echo isset($title) ? "$title : Diving in Focus" : "Diving in Focus" ?></title>
  <meta charset="UTF-8">
</head>
<body>
  <div id="cntl" class="<?php echo $cntl; ?>">
    <div id="actn" class="<?php echo $actn; ?>">
      <div id="content">
        <?php echo $page ?>
      </div>
      <div id="header">
        <ul>  
          <li>Photos</li>
          <li>Videos</li>
          <li>Blogs</li>
        </ul>  
      </div>
      <div id="footer">
      </div>
    </div>
  </div>
</body>
</html>
