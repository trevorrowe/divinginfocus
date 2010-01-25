<?php $this->title('Login'); ?>

<form method='POST' action='<?php echo url('index') ?>'>

  <?php echo text_field_row($user, 'username') ?>

  <?php echo password_field_row($user, 'password') ?>

  <?php echo form_row(checkbox_tag('remember', $params['remember']), array(
    'label' => 'Remember me', 
    'class' => 'checkbox')) ?>

  <?php echo submit_row('Login') ?>

</form>
