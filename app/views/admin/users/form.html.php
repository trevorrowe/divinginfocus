<?php echo $this->text_field_row($user, 'username', array(
  'required' => true,
  'hint' => 'Letters and numbers only')) ?>

<?php echo $this->text_field_row($user, 'email', array('required' => true)) ?>

<?php echo $this->password_field_row($user, 'password', array(
  'hint' => 'If left blank, a password will be auto-generated.')) ?>

<?php echo $this->password_field_row($user, 'password_confirmation') ?>

<?php echo $this->checkbox_row($user, 'admin') ?>

<?php echo $this->submit_row('Save') ?>
