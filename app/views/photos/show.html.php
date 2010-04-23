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

<div id='title'>
  <?php echo $this->title($photo->title) ?>
  <?php echo $this->favorite_photo_link($this->photo) ?>
  <?php if($photo->caption): ?>
    <p id='caption'><?php echo $photo->caption ?></p>
  <?php endif ?>
  <?php if($this->current_user() == $photo->uploader): ?>
    <ul class='edit_links'>
      <li><?php echo $this->link_to('Edit', $this->media_url($photo, 'edit'), array('class' => 'edit')) ?></li>
      <li>
        <?php echo $this->link_to('Identify', $this->media_url($photo, 'identify'), array('class' => 'identify')) ?> |
        <?php echo $this->link_to('Request Help', $this->media_url($photo, 'identify'), array('class' => 'identify')) ?>
      </li>
    </ul>
  <?php endif ?>
</div>

<div id='photo'>
  <?php echo $this->link_to($this->media_thumb($photo, 'medium'), $photo->url('large')) ?>
</div>

<dl class='details table'>
  <dt>Photographer</dt>
  <dd><?php echo $this->user_link($photo->username) ?></dd>

  <?php if($photo->meta): ?>
    <?php if($photo->meta->taken_at || $photo->meta->taken_where): ?>
      <dt>Taken At</dt>
      <?php if($photo->meta->taken_where): ?>
        <dd><?php echo h($photo->meta->taken_where) ?></dd>
      <?php endif ?>
      <?php if($photo->meta->taken_at): ?>
        <dd><?php echo format_datetime($photo->meta->taken_at) ?></dd>
      <?php endif ?>
    <?php endif ?>
    <?php if($photo->meta->camera_model || $photo->meta->camera_make): ?>
      <dt>Camera</dt>
      <?php if($photo->meta->camera_make): ?>
        <dd><?php echo h($photo->meta->camera_make) ?></dd>
      <?php endif ?>
      <?php if($photo->meta->camera_model): ?>
        <dd><?php echo h($photo->meta->camera_model) ?></dd>
      <?php endif ?>
    <?php endif ?>
  <?php endif ?>

  <dt>File size</dt>
  <dd><?php echo format_bytes($photo->size) ?></dd>
  <dt>Uploaded</dt>
  <dd><?php echo format_date($photo->created_at) ?></dd>
</dl>

<div id='comments'>

  <h2>Comments</h2>

  <?php if($this->logged_in()): ?>
  <form method='post' action='<?php echo $this->media_url($photo, 'comment') ?>'>
    <fieldset>
      <legend>Leave your comment here</legend>
      <?php echo $this->text_area($comment, 'text') ?>
      <div><?php echo $this->submit_tag('Comment') ?></div>
    </fieldset>
  </form>
  <?php else: ?>
    <p>You need to <?php echo $this->link_to('log in or signup', '/login') ?> to leave a coment.</p>
  <?php endif ?>

  <?php if($this->comments->total > 0): ?>
  <dl>
    <?php foreach($this->comments as $comment): ?>
    <dt class='avatar'><img src='/images/avatars/missing.jpg' alt='' /></dt>
    <dt class='username'><?php echo $comment->username ?></dt>
    <dd><?php echo h($comment->text) ?></dd>
    <?php endforeach ?>
  </dl>
  <?php endif ?>

</div>

<!--
<dl class='exif table'>
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
