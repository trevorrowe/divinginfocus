<ul id="login_logout">
  <?php if($this->logged_in()): ?>
    <li>Logged in as: <?php echo $this->current_user()->username ?></li>
    <li><?php echo $this->link_to('Logout', url('logout', 'index', null)) ?></li>
  <?php else: ?>
    <li><?php echo $this->link_to('Login', url('login', 'index', null)) ?></li>
    <li><?php echo $this->link_to('Register', url('signup', 'index', null)) ?></li>
  <?php endif; ?>
  <?php if($this->user_can_sudo()): ?>
    <?php if($this->current_user()->admin): ?>
      <?php if(str_begins_with($request->uri, '/admin')): ?>
        <li><?php echo $this->link_to('Public', '/') ?></li>
      <?php else: ?>
        <li><?php echo $this->link_to('Admin', '/admin') ?></li>
      <?php endif; ?>
    <?php endif; ?>
      <li>
        <?php echo $this->link_to('Sudo', '/sudo') ?>
        <?php if($this->user_is_sudoed()): ?>
          <?php echo $this->icon_only_link('revert', 'Login As Yourself', $this->sudo_path(App::$session->sudoer_username)) ?>
        <?php endif; ?>
      </li>
  <?php endif; ?>
</ul>

