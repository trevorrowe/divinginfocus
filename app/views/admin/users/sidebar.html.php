<ul class='nav'>
  <li><?php echo link_to('Search Users', 'index', array('class' => 'index icon')) ?></li>
  <li><?php echo link_to('Add a User', 'new', array('class' => 'add icon')) ?></li>
</ul>

<?php if(isset($user) && !$user->is_new_record()): ?>
  <h2><?php echo $user->username ?></h2>  
  <ul class='nav'>
    <li><?php echo link_to('View', url('show', $user)) ?></li>
    <li><?php echo link_to('Edit', url('edit', $user)) ?></li>
    <li><?php echo link_to('Delete', url('destroy', $user)) ?></li>
  </ul>
<?php endif; ?>
