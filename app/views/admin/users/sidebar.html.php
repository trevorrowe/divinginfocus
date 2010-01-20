<ul class='nav'>
  <li><?php echo link_tag('Search Users', 'index', array('class' => 'index icon')) ?></li>
  <li><?php echo link_tag('Add a User', 'new', array('class' => 'new icon')) ?></li>
</ul>

<?php if(isset($user) && !$user->is_new_record()): ?>
  <h2><?php echo $user->username ?></h2>  
  <ul class='nav'>
    <li><?php echo link_tag('View', url('show', $user), array('class' => 'show icon')) ?></li>
    <li><?php echo link_tag('Edit', url('edit', $user), array('class' => 'edit icon')) ?></li>
    <li><?php echo link_tag('Delete', url('destroy', $user), array('class' => 'destroy icon')) ?></li>
  </ul>
<?php endif; ?>
