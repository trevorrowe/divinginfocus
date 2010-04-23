<?php echo $this->title($user->username . '\'s Photos') ?>

<?php echo $this->paginate($photos) ?>
<div class='photo_quilt'>
<?php foreach($photos as $photo): ?>
  <?php echo $this->linked_media($photo, 'thumb') ?>
<?php endforeach; ?>
</div>
