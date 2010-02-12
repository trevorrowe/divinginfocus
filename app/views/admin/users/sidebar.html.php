<ul class='nav'>
  <li><?php echo $this->icon_link('index', 'Search Users', 'index') ?></li>
  <li><?php echo $this->icon_link('new', 'Add a User', 'new') ?></li>
</ul>

<?php if(isset($user) && !$user->is_new_record()): ?>
  <h2><?php echo $user->username ?></h2>  
  <ul class='nav'>
    <li><?php echo $this->icon_link('show', 'View', url('show', $user)) ?></li>
    <li><?php echo $this->icon_link('edit', 'Edit', url('edit', $user)) ?></li>
    <li><?php echo $this->icon_link('sudo', 'Sudo', url('sudo', 'login_as', $user)) ?></li>
    <li><?php echo $this->icon_link('destroy', 'Delete', url('destroy', $user)) ?></li>
  </ul>
<?php endif; ?>
