<?php echo form_errors($user, 'base') ?>

<?php echo text_row($user, 'username', array(
  'required' => true,
  'hint' => 'letters and numbers only')) ?>

<?php echo text_row($user, 'email', array('required' => true)) ?>

<?php echo password_row($user, 'password', array(
  'hint' => 'if blank, a password will be auto-generated.')) ?>

<?php echo password_row($user, 'password_confirmation') ?>

<?php echo checkbox_row($user, 'admin') ?>

<?php echo submit_row('Save') ?>
