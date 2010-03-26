<?php echo $this->title($user->username) ?>

<?php if($photos->total > 0): ?>
<h2>Photos (<?php echo $photos->total ?>)</h2>
<?php echo $this->photo_quilt($photos, $this->user_path($user, 'photos')) ?>
<?php else: ?>
  <h2>Photos</h2>
  <p><?php echo $user->username ?> has not uploaded any photos yet.</p>
<?php endif ?>

<h2>Videos</h2>

<h2>Albums</h2>
