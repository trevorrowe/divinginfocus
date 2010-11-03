<?php echo $this->title($user->username) ?>

<?php if($photos->total > 0): ?>
<h2>Photos (<?php echo $photos->total ?>)</h2>
<?php echo $this->quilt($photos, $this->user_path($user, 'photos')) ?>
<?php else: ?>
  <h2>Photos</h2>
  <p><?php echo $user->username ?> has not uploaded any photos yet.</p>
<?php endif ?>

<?php if($videos->total > 0): ?>
<h2>Videos (<?php echo $videos->total ?>)</h2>
<?php echo $this->quilt($videos, $this->user_path($user, 'videos')) ?>
<?php else: ?>
  <h2>Videos</h2>
  <p><?php echo $user->username ?> has not uploaded any videos yet.</p>
<?php endif ?>

<?php if($albums->total > 0): ?>
<h2>Albums (<?php echo $albums->total ?>)</h2>
<ul>
  <?php foreach($albums as $album): ?>
    <li><?php echo $this->linked_album($album) ?></li>
  <?php endforeach ?>
</ul>
<?php else: ?>
  <h2>Albums</h2>
  <p><?php echo $user->username ?> has not uploaded any albums yet.</p>
<?php endif ?>
