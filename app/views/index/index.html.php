<h1><?php echo $title = 'Hello World Title'; ?></h1>
<h2>Inflections</h2>
<dl>
  <?php $str = 'cat'; ?>
  <dt><?php echo $str; ?></dt>
  <dd><?php echo pluralize($str, 2); ?></dd>
  <?php $str = 'cats'; ?>
  <dt><?php echo $str; ?></dt>
  <dd><?php echo singularize($str); ?></dd>
</dl>
<pre>
  <?php print_r(flash()); ?>
</pre>
