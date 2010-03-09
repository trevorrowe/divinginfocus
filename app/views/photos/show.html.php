<?php echo $this->title($photo->title) ?>

<!--

* click to favorite (star or something)
* comments 
* links to versions
* link to post to nwdiveclub
* link to exif data (or show on page)
* link to owners feed

for owners

* link to edit
* link to delete
* link to add to albumb

-->

<div id='photo'>
  <?php echo $this->link_to($this->photo_tag($photo, 'medium'), $photo->url('large')) ?>
  <?php if($photo->caption): ?>
    <p id='caption'><?php echo $photo->caption ?></p>
  <?php endif ?>
</div>

<dl id='details'>
  <dt>Photographer</dt>
  <dd><?php echo $this->user_link($photo->owner()) ?></dd>
  <dt>Filename</dt>
  <dd><?php echo h($photo->filename) ?></dd>
  <dt>Uploaded</dt>
  <dd><?php echo format_date($photo->created_at) ?></dd>
  <dt>File size</dt>
  <dd><?php echo format_bytes($photo->size) ?></dd>
</dl>
<?php if($this->current_user() == $photo->owner()): ?>
  <?php echo $this->link_to('Edit Photo', url('edit', $photo), array('class' => 'edit')) ?>
<?php endif ?>

<!--
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
-->
