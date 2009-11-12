<?php header("HTTP/1.0 404 Not Found"); 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <title>404: Page Not Found</title>
   <meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" />
</head>
<body>
  <h1>404: Page Not Found</h1>
  <pre>
    <h2>Action Method</h2>
    <?php print_r($action_method); ?>
    <h2>Params</h2>
    <?php print_r($params); ?>
  </pre>
</body>
</html>
