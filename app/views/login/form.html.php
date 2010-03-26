<form method='post' action='/login/authenticate'>
  <?php echo $this->text_field_row($user, 'username') ?>
  <?php echo $this->password_field_row($user, 'password') ?>
  <?php echo $this->form_row($this->checkbox_tag('remember_me', is_null($params->remember_me) ? true : $params->remember_me), array(
    'label' => 'Remember me', 
    'class' => 'checkbox')) ?>
  <?php echo $this->submit_row('Login') ?>
</form>
