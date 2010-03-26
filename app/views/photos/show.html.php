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
    <?php echo $this->link_to('Edit Photo', $this->photo_url($photo, 'edit'), array('class' => 'edit')) ?>
  <?php endif ?>
</div>

<div id='photo'>
  <?php echo $this->link_to($this->photo_tag($photo, 'medium'), $photo->url('large')) ?>
</div>

<dl class='details table'>
  <dt>Photographer</dt>
  <dd><?php echo $this->user_link($photo->username) ?></dd>
  <dt>Taken</dt>
  <dd>Discovery Bay, Eds Fault</dd>
  <dd><?php echo format_date($photo->created_at) ?></dd>
  <dt>Filename</dt>
  <dd><?php echo h($photo->filename) ?></dd>
  <dt>File size</dt>
  <dd><?php echo format_bytes($photo->size) ?></dd>
  <dt>Uploaded</dt>
  <dd><?php echo format_date($photo->created_at) ?></dd>
</dl>

<?php if($photo->identification): ?>
<h2>Identification</h2>
<dl class='identification table'>
  <dt>Family</dt>
  <dd><?php echo $this->photo->identification->family ?></dd>
  <dt>Common Name</dt>
  <dd><?php echo $this->photo->identification->common_name ?></dd>
  <dt>Scientific Name</dt>
  <dd><?php echo $this->photo->identification->common_name ?></dd>
</dl>
<?php endif ?>

<div id='comments'>
  <h2>Comments</h2>

  <?php if($this->logged_in()): ?>
  <form method='post' action='<?php echo $this->photo_url($photo, 'comment') ?>'>
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
    <dt><?php echo $comment->username ?></dt>
    <dd><?php echo h($comment->text) ?></dd>
    <?php endforeach ?>
  </dl>
  <?php endif ?>
</div>

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
