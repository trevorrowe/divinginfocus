<ul class='nav'>
  <li><?php echo $this->link_to('Search Users', 'index', array('class' => 'index icon')) ?></li>
  <li><?php echo $this->link_to('Add a User', 'new', array('class' => 'new icon')) ?></li>
</ul>

<?php if(isset($user) && !$user->is_new_record()): ?>
  <h2><?php echo $user->username ?></h2>  
  <ul class='nav'>
    <li><?php echo $this->link_to('View', url('show', $user), array('class' => 'show icon')) ?></li>
    <li><?php echo $this->link_to('Edit', url('edit', $user), array('class' => 'edit icon')) ?></li>
    <li><?php echo $this->link_to('Delete', url('destroy', $user), array('class' => 'destroy icon')) ?></li>
  </ul>
<?php endif; ?>
