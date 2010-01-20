<?php $this->title('Login'); ?>

<form method='POST' action=''>
  <?php echo text_field_row($user, 'username') ?>
  <?php echo password_field_row($user, 'password') ?>
  <?php echo submit_button_row('Login') ?>
</form>
