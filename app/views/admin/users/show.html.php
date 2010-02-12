<h1>User Details</h1>

<dl class="table">
  <dt>Username</dt>
  <dd><?php echo h($user->username) ?></dd>
  <dt>Email</dt>
  <dd><?php echo h($user->email) ?></dd>
  <dt>UUID</dt>
  <dd class='uuid'><?php echo $user->uuid ?></dd>
  <dt>Admin</dt>
  <dd class="<?php echo $user->admin ? 'yes' : 'no' ?>"><?php echo format_yes_no($user->admin) ?></dd>
  <dt>Enabled</dt>
  <dd class="<?php echo $user->disabled ? 'no' : 'yes' ?>"><?php echo format_yes_no(!$user->disabled) ?></dd>
  <dt>Verified?</dt>
  <?php if($user->is_verified()): ?>
    <dd class='yes'>Yes</dd>
    <dt>Verified On</dt>
    <dd><?php echo format_datetime($user->verified_at) ?></dd>
  <?php else: ?>
    <dd class='no'>No</dd>
  <?php endif; ?>
  <dt>Created At</dt>
  <dd><?php echo format_datetime($user->created_at) ?></dd>
  <dt>Updated At</dt>
  <dd><?php echo format_datetime($user->updated_at) ?></dd>
</dl>
