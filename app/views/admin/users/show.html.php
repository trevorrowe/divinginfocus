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
  <dd class="<?php echo $user->enabled ? 'yes' : 'no' ?>"><?php echo format_yes_no($user->enabled) ?></dd>
  <dt>Validated?</dt>
  <?php if($user->is_validated()): ?>
    <dd class='yes'>Yes</dd>
    <dt>Validated On</dt>
    <dd><?php echo format_datetime($user->validated_at) ?></dd>
  <?php else: ?>
    <dd class='no'>No</dd>
  <?php endif; ?>
  <dt>Created At</dt>
  <dd><?php echo format_datetime($user->created_at) ?></dd>
  <dt>Updated At</dt>
  <dd><?php echo format_datetime($user->updated_at) ?></dd>
</dl>
