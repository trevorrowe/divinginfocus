<?php echo $this->title('Users') ?>

<?php echo $this->paginate($users) ?>
<ul>
  <?php foreach($users as $user): ?>
    <li><?php echo $this->user_link($user) ?></li>
  <?php endforeach ?>
</ul>
