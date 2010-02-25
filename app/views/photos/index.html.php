<?php echo $this->title = 'Photos' ?>
<div id="primary" class="col">
  <?php foreach($photos as $photo): ?>
    <?php echo $this->photo_link($photo, 'thumb') ?>
  <?php endforeach; ?>
</div>
