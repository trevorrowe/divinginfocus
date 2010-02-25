<?php echo $this->title($photo->title) ?>

<?php echo $this->link_to($this->photo_tag($photo, 'medium'), $photo->url('large')) ?>

<?php if($photo->caption): ?>
  <p id='caption'><?php echo $photo->caption ?></p>
<?php endif ?>

<dl>
<?php foreach($photo->exif_data() as $k => $v): ?>
  <dt><?php echo $k ?></dt>
  <?php if(is_array($v) && $k != 'COMPUTED' && $k != 'THUMBNAIL') debug($v) ?>
  <dd>
    <?php if(is_array($v)): ?>
      <dl>
        <?php foreach($v as $k2 => $v2): ?>
        <dt><?php echo $k2 ?></dt>
        <dd><?php echo $v2 ?></dd>
        <?php endforeach ?>
      </dl>
    <?php else: ?>
      <?php echo $v ?>
    <?php endif ?>
  </dd>
<?php endforeach ?>
</dl>
