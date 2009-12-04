<h1><?php echo $title = 'Hello World Title'; ?></h1>
<ul>
  <li><?php echo Pippa\url('edit', 123); ?></li>
  <li><?php echo Pippa\url('index', 'redirect', 123); ?></li>
  <li><?php echo Pippa\url('redirect'); ?></li>
</ul>
