<?php if($this->user_is_sudoed()): ?>
  <ul class='nav'>
    <li><?php echo $this->icon_link('revert', 'Login As Yourself', url('sudo', 'login_as', App::$session->sudo_id)) ?></li>
  </ul>
<?php endif; ?>
