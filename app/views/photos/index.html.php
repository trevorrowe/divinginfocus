<?php echo $this->title = 'Photos' ?>
<?php echo $this->paginate($photos) ?>
<div class='photo_quilt'>
<?php foreach($photos as $photo): ?>
  <?php echo $this->photo_link($photo, 'thumb') ?>
<?php endforeach; ?>
</div>
