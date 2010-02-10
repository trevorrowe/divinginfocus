<?php echo $this->title('Signup'); ?>

<form method='POST' action=''>
  <?php echo $this->text_field_row($user, 'username') ?>
  <?php echo $this->text_field_row($user, 'email') ?>
  <?php echo $this->password_field_row($user, 'password') ?>
  <?php echo $this->password_field_row($user, 'password_confirmation') ?>
  <?php echo $this->submit_row('Signup') ?>
</form>
