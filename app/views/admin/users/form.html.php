<?php echo base_errors($user) ?>

<?php echo text_field_row($user, 'username', array(
  'required' => true,
  'hint' => 'Letters and numbers only')) ?>

<?php echo text_field_row($user, 'email', array('required' => true)) ?>

<?php echo password_field_row($user, 'password', array(
  'hint' => 'If left blank, a password will be auto-generated.')) ?>

<?php echo password_field_row($user, 'password_confirmation') ?>

<?php echo checkbox_row($user, 'admin') ?>

<?php echo submit_row('Save') ?>
