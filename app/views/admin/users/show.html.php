<h1>User Details</h1>

<dl class="table">
  <dt>Username</dt>
  <dd><?php echo h($user->username) ?></dd>
  <dt>Email</dt>
  <dd><?php echo h($user->email) ?></dd>
  <dt>Admin</dt>
  <dd class="<?php echo $user->admin ? 'yes' : 'no' ?>"><?php echo format_yes_no($user->admin) ?></dd>
</dl>
