<ul id="login_logout">

  <?php if($this->user_can_sudo()): ?>

      <li>
        <?php echo $this->link_to('Sudo', '/sudo') ?>
        <?php if($this->user_is_sudoed()): ?>
          <?php echo $this->icon_only_link('revert', 'Login As Yourself', $this->sudo_path(App::$session->sudoer_username)) ?>
        <?php endif; ?>
      </li>

  <?php endif; ?>
</ul>

