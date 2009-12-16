<h1>Users</h1>
<ul>
  <?php foreach($users as $user): ?>
    <li><?php echo link_to($user->username, url('show', $user)) ?></li>  
  <?php endforeach; ?>
</ul>
